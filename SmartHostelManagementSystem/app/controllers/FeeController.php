<?php
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../helpers/response.php';

class FeeController {
    public function list() {
        $fees = Fee::all();
        return jsonSuccess($fees);
    }

    public function get($id) {
        $fee = Fee::find($id);
        if (!$fee) {
            return jsonError('Fee not found', 404);
        }
        return jsonSuccess($fee);
    }

    public function create($data) {
        $errors = validate($data, [
            'student_id' => 'required|exists:students',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        $id = Fee::create($data);
        $fee = Fee::find($id);
        return jsonSuccess($fee, 'Fee created successfully');
    }

    public function update($id, $data) {
        $fee = Fee::find($id);
        if (!$fee) {
            return jsonError('Fee not found', 404);
        }

        $errors = validate($data, [
            'amount' => 'numeric|min:1',
            'due_date' => 'date',
            'status' => 'in:paid,unpaid,overdue'
        ]);

        if (!empty($errors)) {
            return jsonError('Validation failed', 400, $errors);
        }

        Fee::update($id, $data);
        return jsonSuccess(null, 'Fee updated successfully');
    }

    public function delete($id) {
        $fee = Fee::find($id);
        if (!$fee) {
            return jsonError('Fee not found', 404);
        }
        Fee::delete($id);
        return jsonSuccess(null, 'Fee deleted successfully');
    }

    public function markPaid($id) {
        $fee = Fee::find($id);
        if (!$fee) {
            return jsonError('Fee not found', 404);
        }
        Fee::markPaid($id);
        return jsonSuccess(null, 'Fee marked as paid');
    }
}
?>