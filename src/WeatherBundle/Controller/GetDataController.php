<?php

namespace WeatherBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WeatherBundle\Entity\City;
use WeatherBundle\Form\CityType;

class GetDataController extends Controller
{
    /**
     * @Route("/get", name="get")
     */
    public function getAction(Request $request)
    {
        $cityRepository = $this->getDoctrine()->getRepository('WeatherBundle:City');
        $cities = $cityRepository->findAll();
        $city = null;

        if (!$cities) {
            throw new NotFoundHttpException('error could not load database');
        }

        if ($request->request->get('selectCity')) {
            $selectedCity = $request->request->get('selectCity');
            $selectedCiteExploded = explode('|', $selectedCity);
            $code = $selectedCiteExploded[0];
            $city = $selectedCiteExploded[1];
            $session = $request->getSession();
            $session->set('cityCode', $code);
        }

        return $this->render('WeatherBundle:Weather:get.html.twig', array(
            'cities' => $cities,
            'selectedCity' => $city
        ));
    }

    /**
     * @Route("/ajax", name="ajax")
     */
    public function ajaxAction(Request $request)
    {
        $session = $request->getSession();
        $cityCode = $session->get('cityCode');
        $value = file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=" . $cityCode . "&lang=pl&units=metric&APPID=353e958b71e5f8b5e61e57ddcafb983c");
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

        if ($form->isSubmitted()) {
            $city = $form->getData();
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
     * @Route("/delete")
     */
    public function editAction(Request $request)
    {
        $cityRepository = $this->getDoctrine()->getRepository('WeatherBundle:City');
        $cities = $cityRepository->findAll();

        if (!$cities) {
            throw new NotFoundHttpException('error could not load database');
        }

        return $this->render('WeatherBundle:Weather:delete.html.twig', array(
            'cities' => $cities
        ));
    }

    /**
     * @Route("/findCity")
     */
    public function findCityAction()
    {
        $value = file_get_contents("http://openweathermap.org/help/city_list.txt");
        if (strpos($value, 'Bedzin') !== false) {
            $cityList = explode(PHP_EOL, $value);
            $matches = preg_grep('~Bedzin~', $cityList);
            var_dump($matches);
            $key = key($matches);
            var_dump($key);
            preg_match('~[0-9]{7}~', $matches[$key], $match);
            var_dump($match);
            return new Response("Działam");

        }

    }
}
