<?php
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../helpers/response.php';

class ReportController {
    public function occupancy() {
        $report = db()->query("SELECT room_number, block, floor, capacity, current_occupancy, 
                               (current_occupancy / capacity * 100) as occupancy_percent 
                               FROM rooms")->fetchAll();
        return jsonSuccess($report);
    }

    public function fees() {
        $report = Fee::all();
        return jsonSuccess($report);
    }

    public function complaints() {
        $report = Complaint::getStatistics();
        return jsonSuccess($report);
    }

    public function students() {
        $report = db()->query("SELECT s.full_name, s.student_id, r.room_number, 
                              (SELECT COUNT(*) FROM fees WHERE student_id=s.id AND status='unpaid') as unpaid_fees,
                              (SELECT COUNT(*) FROM complaints WHERE student_id=s.id AND status!='resolved') as open_complaints
                              FROM students s LEFT JOIN rooms r ON s.room_id = r.id")->fetchAll();
        return jsonSuccess($report);
    }

    public function summary() {
        $report = [
            'total_students' => Student::count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::countAvailable(),
            'occupied_rooms' => Room::countOccupied(),
            'pending_complaints' => Complaint::countPending(),
            'unpaid_fees' => Fee::countUnpaid(),
            'total_collected' => Fee::getTotalCollected(),
            'total_due' => Fee::getTotalDue()
        ];
        return jsonSuccess($report);
    }
}
?>