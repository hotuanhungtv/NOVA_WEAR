<?php
$errors = (array) (session()->getFlashdata('form_errors') ?? []);
foreach (['success', 'danger', 'warning', 'info'] as $k) {
    if ($m = session()->getFlashdata($k)) {
        echo '<div class="alert alert-' . $k . ' alert-dismissible fade show">' . e($m) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
if ($errors) {
    echo '<div class="alert alert-danger"><ul class="mb-0 ps-3">';
    foreach ($errors as $er) { echo '<li>' . e($er) . '</li>'; }
    echo '</ul></div>';
}
