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
        if ($request->request->get('add')) {
            $city = new City();
            $session = $request->getSession();
            $city->setName($session->get('city'));
            $city->setCode($session->get('code'));
            var_dump($city);
            $em = $this->getDoctrine()->getManager();
            $em->persist($city);
            $em->flush();
            return $this->redirectToRoute('get');
        }

        return $this->render('WeatherBundle:Weather:create.html.twig', array());
    }

    /**
     * @Route("/delete")
     */
    public function editAction(Request $request)
    {

        if ($request->request->get('cityToDelete')) {
            $em = $this->getDoctrine()->getManager();
            $cityToDelete = $em->getRepository('WeatherBundle:City')->find($request->request->get('cityToDelete'));
            $em->remove($cityToDelete);
            $em->flush();
            return $this->redirectToRoute('get');
        }
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
    public function findCityAction(Request $request)
    {
        if ($request->request->get('city')) {
            $cityName = $request->request->get('city');
            $cityName = str_replace(array('ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż'), array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z'), $cityName);
            $cityName = strtolower($cityName);
            $cityName = explode(' ', $cityName);
            for ($i = 0; $i != count($cityName); $i++) {
                $cityName[$i] = ucfirst($cityName[$i]);
            }
            $cityName = implode(' ', $cityName);
            $value = file_get_contents("http://openweathermap.org/help/city_list.txt");

            if (strpos($value, $cityName) !== false) {
                $cityList = explode(PHP_EOL, $value);
                $matches = preg_grep("~$cityName~", $cityList);
                $key = key($matches);
                preg_match('~[0-9]{6,7}~', $matches[$key], $match);
                $session = $request->getSession();
                $session->set('city', $cityName);
                $session->set('code', $match[0]);
                return $this->render('WeatherBundle:Weather:create.html.twig', array(
                    'cityCode' => $match,
                    'cityName' => $cityName
                ));
            }
        }
    }

    /**
     * @Route("/adminPanel")
     */
    public function adminPanelAction()
    {
        return $this->render('WeatherBundle:Weather:adminPanel.html.twig', array(

        ));
    }
}
