<?php
namespace App\Controller;

use App\Entity\Call;
use App\Repository\CallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTime;

include_once __DIR__.'/../PhoneCountries.php';
// require_once(MAX_PREFIX_LENGTH.__DIR__.'/../PhoneCountries.php');

class CallsController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }

    public function calls(Request $request, CallRepository $callRepository): Response
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
            return new Response(
                '<html><body>Num of rows: '.$row.'</body></html>'
            );
        }

        // select total.customer_id, total_same.total_calls_same, total_same.duration_same, total.total_calls, total.duration from
        // (select distinct customer_id, count(*) as total_calls, sum(duration) as duration from bitnami_myapp.call group by customer_id) as total
        // full outer join
        // (select distinct customer_id, count(*) as total_calls_same, sum(duration) as duration_same from bitnami_myapp.call where customer_ip = dialed_number group by customer_id) as total_same on total.customer_id = total_same.customer_id
        if ($request->isMethod('GET'))
        {
            // $calls = $callRepository->getCallStats();
            $call_stats = array();
            $calls = $callRepository->findAll();
            foreach ($calls as $call) {
                // Convert phone to a continent code
                $phone = $call->getDialedNumber();
                $phone_code = $phone;
                for ($i = 1; $i < MAX_PREFIX_LENGTH+1; $i++) {
                    $phone_key = substr($phone, 0, $i);
                    if (array_key_exists($phone_key, PHONE_COUNTRIES)) {
                        $phone_code = COUNTRY_CONTINENTS[PHONE_COUNTRIES[$phone_key]];
                        break;
                    } 
                }

                // Convert customer IP to a continent code
                $customer_ip = $call->getCustomerIp();
                $response = $this->client->request('GET', 'http://api.ipstack.com/'.$customer_ip.'?access_key=ed09e98ccc0c3f163c4d575a764f3629');
                $json_data = json_decode($response->getContent(), true);
                $ip_code = $json_data['continent_code'];

                $customer_id = $call->getCustomerId();
                $duration = $call->getDuration();
                $stats = $call_stats[$customer_id] ?? array('same_calls' => 0, 'same_duration' => 0, 'total_calls' => 0, 'total_duration' => 0);
                $stats['total_calls'] += 1;
                $stats['total_duration'] += $duration;
                if ($phone_code == $ip_code) {
                    $stats['same_calls'] += 1;
                    $stats['same_duration'] += $duration;
                }
                $call_stats[$customer_id] = $stats;
            }

            return new Response(
                '<html><body>The 6434 calls: '.$call_stats['6434']['total_calls'].'</body></html>'
            );
        }
    }
}
