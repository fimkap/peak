<?php
namespace App\Controller;

use App\Entity\Call;
use App\Repository\CallRepository;
use App\Message\CallStats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

const FILE_FORM_KEY = 'callsfile';

class CallsController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function calls(Request $request, CallRepository $callRepository, MessageBusInterface $bus): Response
    {
        if ($request->isMethod('POST'))
        {
            try {
                $entityManager = $this->getDoctrine()->getManager();

                $file = $request->files->get(FILE_FORM_KEY);
                $rows = 0;
                if (($handle = fopen($file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $date = new DateTime($data[1]);
                        $call_found = $callRepository->findOneBy([
                            'customer_id' => $data[0],
                            'date' => $date,
                        ]);
                        if (!$call_found) {
                            $call = new Call();
                            $call->setCustomerId($data[0]);
                            $call->setDate($date);
                            $call->setDuration(intval($data[2]));
                            $call->setDialedNumber($data[3]);
                            $call->setCustomerIp($data[4]);
                            $entityManager->persist($call);
                            $rows++;
                        }
                    }
                    $entityManager->flush();
                    fclose($handle);
                }

                $bus->dispatch(new CallStats($callRepository, $this->client));

                return new Response('Saved rows: '.$rows);
            } catch(\Exception $e) {
                return new Response('Error has occured: '.$e->getMessage(), 400);
            }
        }
    }
}
