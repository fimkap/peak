<?php
namespace App\Controller;

use App\Entity\Call;
use App\Repository\CallRepository;
use App\Message\CallStats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

// include_once __DIR__.'/../PhoneCountries.php';

class CallsController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function calls(Request $request, CallRepository $callRepository, PublisherInterface $publisher, MessageBusInterface $bus): Response
    {
        if ($request->isMethod('POST'))
        {
            $entityManager = $this->getDoctrine()->getManager();

            $file = $request->files->get('callsfile');
            $row = 1;
            if (($handle = fopen($file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $call = new Call();
                    $call->setCustomerId($data[0]);
                    $call->setDate(new DateTime($data[1]));
                    $call->setDuration(intval($data[2]));
                    $call->setDialedNumber($data[3]);
                    $call->setCustomerIp($data[4]);
                    $entityManager->persist($call);
                    $row++;
                }
                $entityManager->flush();
                fclose($handle);
            }

            $bus->dispatch(new CallStats($callRepository, $this->client));

            return new Response(
                '<html><body>Num of rows: '.$row.'</body></html>'
            );
        }

        // select total.customer_id, total_same.total_calls_same, total_same.duration_same, total.total_calls, total.duration from
        // (select distinct customer_id, count(*) as total_calls, sum(duration) as duration from bitnami_myapp.call group by customer_id) as total
        // full outer join
        // (select distinct customer_id, count(*) as total_calls_same, sum(duration) as duration_same from bitnami_myapp.call where customer_ip = dialed_number group by customer_id) as total_same on total.customer_id = total_same.customer_id
    }
}
