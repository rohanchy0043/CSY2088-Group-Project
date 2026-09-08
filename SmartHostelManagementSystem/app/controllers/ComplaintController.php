<?php
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../helpers/response.php';

class ComplaintController {
    public function list() {
        $complaints = Complaint::all();
        return jsonSuccess($complaints);
    }

    public function get($id) {
        $complaint = Complaint::find($id);
        if (!$complaint) {
            return jsonError('Complaint not found', 404);
        }
        return jsonSuccess($complaint);
    }

    public function create($data) {
        $errors = validate($data, [
            'student_id' => 'required|exists:students',
            'category' => 'required',
            'subject' => 'required|min:5',
            'description' => 'required|min:10'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        $id = Complaint::create($data);
        $complaint = Complaint::find($id);
        return jsonSuccess($complaint, 'Complaint created successfully');
    }

    public function update($id, $data) {
        $complaint = Complaint::find($id);
        if (!$complaint) {
            return jsonError('Complaint not found', 404);
        }

        $errors = validate($data, [
            'status' => 'in:pending,in-progress,resolved',
            'priority' => 'in:low,medium,high'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        Complaint::update($id, $data);
        return jsonSuccess(null, 'Complaint updated successfully');
    }

    public function delete($id) {
        $complaint = Complaint::find($id);
        if (!$complaint) {
            return jsonError('Complaint not found', 404);
        }
        Complaint::delete($id);
        return jsonSuccess(null, 'Complaint deleted successfully');
    }

    public function resolve($id) {
        $complaint = Complaint::find($id);
        if (!$complaint) {
            return jsonError('Complaint not found', 404);
        }
        Complaint::resolve($id, currentUserId());
        return jsonSuccess(null, 'Complaint resolved successfully');
    }
}
?>