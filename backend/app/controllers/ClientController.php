<?php
class ClientController extends Controller
{
    private ClientModel $clients;
    private AppointmentModel $appointments;
    private ServiceModel $services;

    public function __construct()
    {
        $this->clients      = new ClientModel();
        $this->appointments = new AppointmentModel();
        $this->services     = new ServiceModel();
    }

    // GET /client/dashboard
    public function dashboard(array $params): void
    {
        $user   = $this->currentUser();
        $client = $this->clients->findByUserId($user['id']);

        $appointments = $client
            ? $this->appointments->getByClient((int) $client['id'])
            : [];

        include BASE_PATH . '/app/views/client/dashboard.php';
    }

    // GET /client/book
    public function bookingForm(array $params): void
    {
        $user     = $this->currentUser();
        $services = $this->services->getAllWithCategories(true);

        include BASE_PATH . '/app/views/client/book.php';
    }
}
