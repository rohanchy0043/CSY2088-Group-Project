<?php
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../helpers/response.php';

class RoomController {
    public function list() {
        $rooms = Room::all();
        return jsonSuccess($rooms);
    }

    public function get($id) {
        $room = Room::find($id);
        if (!$room) {
            return jsonError('Room not found', 404);
        }
        return jsonSuccess($room);
    }

    public function create($data) {
        $errors = validate($data, [
            'room_number' => 'required|unique:rooms',
            'block' => 'required',
            'floor' => 'required|numeric',
            'capacity' => 'required|numeric|min:1'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        $id = Room::create($data);
        $room = Room::find($id);
        return jsonSuccess($room, 'Room created successfully');
    }

    public function update($id, $data) {
        $room = Room::find($id);
        if (!$room) {
            return jsonError('Room not found', 404);
        }

        $errors = validate($data, [
            'room_number' => 'required',
            'block' => 'required',
            'floor' => 'required|numeric',
            'capacity' => 'required|numeric|min:1',
            'status' => 'in:available,occupied,maintenance,full'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        Room::update($id, $data);
        return jsonSuccess(null, 'Room updated successfully');
    }

    public function delete($id) {
        $room = Room::find($id);
        if (!$room) {
            return jsonError('Room not found', 404);
        }
        Room::delete($id);
        return jsonSuccess(null, 'Room deleted successfully');
    }
}
?>