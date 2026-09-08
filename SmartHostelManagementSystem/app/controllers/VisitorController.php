<?php
require_once __DIR__ . '/../models/Visitor.php';
require_once __DIR__ . '/../helpers/response.php';

class VisitorController {
    public function list() {
        $visitors = Visitor::all();
        return jsonSuccess($visitors);
    }

    public function get($id) {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return jsonError('Visitor not found', 404);
        }
        return jsonSuccess($visitor);
    }

    public function create($data) {
        $errors = validate($data, [
            'student_id' => 'required|exists:students',
            'visitor_name' => 'required|min:2',
            'contact' => 'required|min:7',
            'purpose' => 'required'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        $id = Visitor::create($data);
        $visitor = Visitor::find($id);
        return jsonSuccess($visitor, 'Visitor created successfully');
    }

    public function update($id, $data) {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return jsonError('Visitor not found', 404);
        }

        $errors = validate($data, [
            'status' => 'in:pending,approved,rejected,checked_in,checked_out'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        Visitor::update($id, $data);
        return jsonSuccess(null, 'Visitor updated successfully');
    }

    public function delete($id) {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return jsonError('Visitor not found', 404);
        }
        Visitor::delete($id);
        return jsonSuccess(null, 'Visitor deleted successfully');
    }
}
?>