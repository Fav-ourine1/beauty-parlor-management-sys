<?php
class PagesController extends Controller
{
    public function about(array $params): void
    {
        include BASE_PATH . '/app/views/pages/about.php';
    }

    public function contact(array $params): void
    {
        include BASE_PATH . '/app/views/pages/contact.php';
    }
}
