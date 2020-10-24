<?php
namespace App\Controller;

use App\Entity\Call;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class CallsController extends AbstractController
{
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }

    public function calls(Request $request): Response
    {
        if ($request->isMethod('POST'))
        {
            $entityManager = $this->getDoctrine()->getManager();

            $file = $request->files->get('callsfile');
            $row = 1;
            if (($handle = fopen($file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    // check num
                    $call = new Call();
                    $call->setCustomerId($data[0]);
                    // $call->setDate(date("Y-m-d H:i:s", strtotime($data[1])));
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
    }
}
