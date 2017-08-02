<?php

namespace WeatherBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetDataController extends Controller
{
    /**
     * @Route("/get")
     */
    public function getAction()
    {
        return $this->render('WeatherBundle:Weather:get.html.twig', array());
    }

    /**
     * @Route("/ajax", name="ajax")
     */
    public function ajaxAction(Request $request)
    {
        $value = file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=3100796&lang=pl&units=metric&APPID=353e958b71e5f8b5e61e57ddcafb983c");
        return new JsonResponse($value);
    }
}
