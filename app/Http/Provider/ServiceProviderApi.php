<?php

namespace App\Http\Provider;

use GuzzleHttp\Client as GuzzleHttpClient;

class Client
{
    const REQUEST_BASE_PATH = 'http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/';
   // const REQUEST_BASE_PATH_V1 = 'https://www.mobuss.com.br/ccweb/rest/v1/assistencia/solicitacao';
    
    private $httpClient;
    private $companyId;

    public function __construct(GuzzleHttpClient $httpClient, string $companyId)
    {
        $this->httpClient = $httpClient;
        $this->companyId = $companyId;
    }

    public function getProcedencia(): array
    {
        $response = $this->httpClient->get(self::REQUEST_BASE_PATH.'/procedencia');

        $body = json_decode($response->getBody());

        return $body;
    }

    public function getUnits(string $buildingId): array
    {
        $response = $this->httpClient->post(self::REQUEST_BASE_PATH.'/consultarLocaisObra', [
            'json' => [
                'idEmpresa' => $this->companyId,
                'idObra' => $buildingId,
            ],
        ]);

        $body = json_decode($response->getBody());

        return $body->locais;
    }

    public function createRequest(array $data): object
    {
        $response = $this->httpClient
            ->post(self::REQUEST_BASE_PATH.'/incluirSolicitacao', [
                'json' => $data,
            ])
        ;

        return json_decode($response->getBody());
    }

    public function getCustomer(string $document): object
    {
        $response = $this->httpClient->post(self::REQUEST_BASE_PATH.'/consultarCliente', [
            'json' => [
                'idEmpresa' => $this->companyId,
                'cpfCnpj' => $document,
            ],
        ]);

        return json_decode($response->getBody());
    }

    public function getCustomerSolicitations(string $document): object
    {   
        $client = new GuzzleHttpClient();
        $response = $client->post(self::REQUEST_BASE_PATH_V1.'/consultarSolicitacoesCliente', [
            'headers' => [
                'Authorization'=> 'Bearer ' . self::REQUEST_TOKEN_V1
            ],
            'json' => [
                'numCPFCNPJ' => $document,
            ],
        ]);

        return json_decode($response->getBody());
    }

    public function getSolicitationInfo(string $solicitaton_id): object
    {
        $client = new GuzzleHttpClient();
        $response = $client->post(self::REQUEST_BASE_PATH_V1.'/consultarSolicitacaoAtendimento', [
            'headers' => [
                'Authorization'=> 'Bearer ' . self::REQUEST_TOKEN_V1
            ],
            'json' => [
                'idSolicitacaoAtendimento' => $solicitaton_id,
            ],
        ]);

        return json_decode($response->getBody());
    }


    public function getSchedule ($data)
    {
        $response = $this->httpClient        
            ->post(self::REQUEST_BASE_PATH_NEW.'/consultarDisponibilidadeAgendamento', [                
            'headers' => [
                'Authorization'=> 'Bearer ' . self::REQUEST_TOKEN_NEW
            ],
                'json' => $data,                
            ]);

        return json_decode($response->getBody());
    }

    public function createSchedule ($data)
    {
        $response = $this->httpClient        
            ->post(self::REQUEST_BASE_PATH_NEW.'/incluirAgendamentoVisitaSolicitacao', [                
            'headers' => [
                'Authorization'=> 'Bearer ' . self::REQUEST_TOKEN_NEW
            ],
                'json' => $data,                
            ]);

        return json_decode($response->getBody());
    }
}
