<?php
namespace App\Message;

use App\Repository\CallRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\Publisher;

include_once __DIR__.'/../PhoneCountries.php';

const MERCURE_JWT_TOKEN='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.Oo0yg7y4yMa1vr_bziltxuTCqb8JVHKxp-f_FwwOim0';
const IP_STACK_ACCESS_KEY='ed09e98ccc0c3f163c4d575a764f3629';

final class MyJwtProvider
{
    public function __invoke(): string
    {
        return MERCURE_JWT_TOKEN;
    }
}

class CallStats
{
    private $callRepository;
    private $cache;
    private $client;

    public function __construct(CallRepository $callRepository, HttpClientInterface $client)
    {
        $this->callRepository = $callRepository;
        $this->cache = new FilesystemAdapter();
        $this->client = $client;
    }

    public function getStats()
    {
        $call_stats = array();
        $calls = $this->callRepository->findAll();
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
            $ip_code = '';
            // $this->cache->delete($customer_ip);
            $ip_code_cache = $this->cache->getItem($customer_ip);
            if (!$ip_code_cache->isHit()) {
                $response = $this->client->request(
                    'GET',
                    'http://api.ipstack.com/'.$customer_ip.'?access_key='.IP_STACK_ACCESS_KEY
                );
                $json_data = json_decode($response->getContent(), true);
                $ip_code = $json_data['continent_code'];
                $ip_code_cache->expiresAfter(3600);
                $this->cache->save($ip_code_cache->set($ip_code));
            } else {
                $ip_code = $ip_code_cache->get();
            }

            $customer_id = $call->getCustomerId();
            $duration = $call->getDuration();
            $stats = $call_stats[$customer_id] ??
                array('same_calls' => 0, 'same_duration' => 0, 'total_calls' => 0, 'total_duration' => 0);
            $stats['total_calls'] += 1;
            $stats['total_duration'] += $duration;
            if ($phone_code == $ip_code) {
                $stats['same_calls'] += 1;
                $stats['same_duration'] += $duration;
            }
            $call_stats[$customer_id] = $stats;
        }

        $publisher = new Publisher('http://192.168.1.150:3000/.well-known/mercure', new MyJwtProvider());
        $update = new Update(
            'http://commpeak.com/calls/1',
            json_encode($call_stats)
        );
        $publisher($update);
    }
}
