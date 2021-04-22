<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BaseController
 * @package App\Controller
 */
abstract class BaseController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * TransformHelper constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $data
     * @return JsonResponse
     */
    protected function response($data): JsonResponse
    {
        return $this->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getDataFromRequest(Request $request)
    {
        $content = $request->getContent();
        $data = [];

        if ($content) {
            $data = json_decode($content, true);
        }

        return $data;
    }
}
