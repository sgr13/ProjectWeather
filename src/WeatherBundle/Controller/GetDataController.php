<?php

namespace WeatherBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WeatherBundle\Entity\City;
use WeatherBundle\Form\CityType;

class GetDataController extends Controller
{
    /**
     * @Route("/get", name="get")
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

    /**
     * @Route("/add")
     */
    public function addAction(Request $request)
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $city = $form->getData();
            var_dump($city);
            $em = $this->getDoctrine()->getManager();
            $em->persist($city);
            $em->flush();
            return $this->redirectToRoute('get');
        }

        return $this->render('WeatherBundle:Weather:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/edit")
     */
    public function editAction()
    {

    }
}
