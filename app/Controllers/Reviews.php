<?php

namespace App\Controllers;

use App\Models\ReviewModel;

/** Khách hàng viết / sửa / xóa đánh giá của chính mình. */
class Reviews extends BaseController
{
    private function backTo(int $productId)
    {
        return redirect()->to(base_url('products/' . $productId) . '#reviews');
    }

    public function save(int $productId)
    {
        $product = $this->db->table('products')
            ->select('ProductID')->where('ProductID', $productId)->where('IsActive', 1)
            ->get()->getRowArray();

        if (! $product) {
            return redirect()->to('/products')->with('danger', 'Sản phẩm không tồn tại.');
        }

        $customerId = $this->customerId();
        $reviews    = new ReviewModel();

        if (review_requires_purchase() && ! $reviews->hasPurchased($productId, $customerId)) {
            return $this->backTo($productId)->with('danger', 'Chỉ khách đã mua sản phẩm này mới được đánh giá.');
        }

        $rules = [
            'rating'  => 'required|in_list[1,2,3,4,5]',
            'title'   => 'permit_empty|max_length[120]',
            'comment' => 'required|min_length[10]|max_length[1000]',
        ];

        $messages = [
            'rating'  => ['required' => 'Vui lòng chọn số sao.', 'in_list' => 'Số sao không hợp lệ.'],
            'title'   => ['max_length' => 'Tiêu đề tối đa 120 ký tự.'],
            'comment' => [
                'required'   => 'Vui lòng nhập nội dung nhận xét.',
                'min_length' => 'Nhận xét cần ít nhất 10 ký tự.',
                'max_length' => 'Nhận xét tối đa 1000 ký tự.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->backTo($productId)
                ->withInput()
                ->with('review_errors', $this->validationErrors());
        }

        $reviews->saveReview(
            $productId,
            $customerId,
            (int) $this->request->getPost('rating'),
            trim((string) $this->request->getPost('title')),
            trim((string) $this->request->getPost('comment'))
        );

        return $this->backTo($productId)->with('success', 'Cảm ơn bạn! Đánh giá của bạn đã được ghi nhận.');
    }

    public function delete(int $reviewId)
    {
        $review = $this->db->table('reviews')
            ->where('ReviewID', $reviewId)
            ->where('CustomerID', $this->customerId())
            ->get()->getRowArray();

        if (! $review) {
            return redirect()->to('/products')->with('danger', 'Không tìm thấy đánh giá.');
        }

        $this->db->table('reviews')->where('ReviewID', $reviewId)->delete();

        return $this->backTo((int) $review['ProductID'])->with('success', 'Đã xóa đánh giá của bạn.');
    }
}
