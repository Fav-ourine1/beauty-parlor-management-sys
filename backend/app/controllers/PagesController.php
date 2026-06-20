<?php
class PagesController extends Controller
{
    public function home(array $params): void
    {
        if (!empty($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
            redirect(APP_URL . "/$role/dashboard");
        }

        $services   = new ServiceModel();
        $categories = $services->getCategories();

        include BASE_PATH . '/app/views/pages/home.php';
    }

    public function about(array $params): void
    {
        include BASE_PATH . '/app/views/pages/about.php';
    }

    public function contact(array $params): void
    {
        include BASE_PATH . '/app/views/pages/contact.php';
    }
}
