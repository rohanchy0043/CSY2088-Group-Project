<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

class Room {
    public static function find($id) {
        $stmt = db()->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByNumber($number) {
        $stmt = db()->prepare("SELECT * FROM rooms WHERE room_number = ?");
        $stmt->execute([$number]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO rooms (room_number, block, floor, capacity, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['room_number'],
            $data['block'],
            $data['floor'],
            $data['capacity'],
            $data['status'] ?? ROOM_AVAILABLE
        ]);
        return db()->lastInsertId();
    }

    public static function update($id, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        $params[] = $id;
        $stmt = db()->prepare("UPDATE rooms SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all() {
        $stmt = db()->query("SELECT * FROM rooms ORDER BY block, floor, room_number");
        return $stmt->fetchAll();
    }

    public static function available() {
        $stmt = db()->query("SELECT * FROM rooms WHERE status = 'available' AND current_occupancy < capacity");
        return $stmt->fetchAll();
    }

    public static function count() {
        return db()->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    }

    public static function countAvailable() {
        return db()->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn();
    }

    public static function countOccupied() {
        return db()->query("SELECT COUNT(*) FROM rooms WHERE current_occupancy > 0")->fetchColumn();
    }

    public static function countMaintenance() {
        return db()->query("SELECT COUNT(*) FROM rooms WHERE status = 'maintenance'")->fetchColumn();
    }

    public static function getOccupancyRate() {
        $total = self::count();
        $capacity = (int) db()->query("SELECT COALESCE(SUM(capacity), 0) FROM rooms")->fetchColumn();
        $occupancy = (int) db()->query("SELECT COALESCE(SUM(current_occupancy), 0) FROM rooms")->fetchColumn();
        return $capacity > 0 ? round(($occupancy / $capacity) * 100, 2) : 0;
    }

    public static function updateOccupancy($roomId) {
        $stmt = db()->prepare("UPDATE rooms SET current_occupancy = (SELECT COUNT(*) FROM students WHERE room_id = ?) WHERE id = ?");
        return $stmt->execute([$roomId, $roomId]);
    }

    public static function updateStatus($roomId) {
        $room = self::find($roomId);
        if (!$room) return false;
        
        $newStatus = ROOM_AVAILABLE;
        if ($room['current_occupancy'] >= $room['capacity']) {
            $newStatus = ROOM_FULL;
        } elseif ($room['current_occupancy'] > 0) {
            $newStatus = ROOM_OCCUPIED;
        } else {
            $newStatus = ROOM_AVAILABLE;
        }
        
        $stmt = db()->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $roomId]);
    }
}
?>