<?php

namespace App\Controllers\Admin;

/** Kiểm duyệt nhận xét & đánh giá (nhân viên + admin). */
class Reviews extends BaseAdmin
{
    public function index(): string
    {
        $show   = (string) $this->request->getGet('show');       // all | visible | hidden
        $rating = (int) $this->request->getGet('rating');

        $b = $this->db->table('reviews r')
            ->select('r.*, p.ProductName, p.ProductCode, c.CustomerName')
            ->join('products p', 'p.ProductID = r.ProductID')
            ->join('customers c', 'c.CustomerID = r.CustomerID');

        if ($show === 'visible') {
            $b->where('r.IsVisible', 1);
        } elseif ($show === 'hidden') {
            $b->where('r.IsVisible', 0);
        } else {
            $show = 'all';
        }

        if ($rating >= 1 && $rating <= 5) {
            $b->where('r.Rating', $rating);
        } else {
            $rating = 0;
        }

        return view('admin/reviews/index', [
            'pageTitle' => 'Đánh giá của khách',
            'reviews'   => $b->orderBy('r.CreatedAt', 'DESC')->limit(200)->get()->getResultArray(),
            'show'      => $show,
            'rating'    => $rating,
        ]);
    }

    public function toggle(int $id)
    {
        $this->db->query('UPDATE reviews SET IsVisible = NOT IsVisible WHERE ReviewID = ?', [$id]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái hiển thị.');
    }

    public function delete(int $id)
    {
        $this->db->table('reviews')->where('ReviewID', $id)->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá.');
    }
}
