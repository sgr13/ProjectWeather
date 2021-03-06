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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class GetDataController extends Controller
{
    /**
     * @Route("/get", name="get")
     */
    public function getAction(Request $request)  //1. Show weather box
    {
        //1.1 Get all cities in DB and show them in select form
        $cityRepository = $this->getDoctrine()->getRepository('WeatherBundle:City');
        $cities = $cityRepository->findAll();
        $city = null;

        if (!$cities) {
            throw new NotFoundHttpException('error could not load database');
        }

        //1.2 get selected data, split them and save city code to session, and city name pass to twig
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
    public function ajaxAction(Request $request) // connect with openweather.org to update weather condtitions
    {
        $session = $request->getSession();
        $cityCode = $session->get('cityCode');
        $value = file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=" . $cityCode . "&lang=pl&units=metric&APPID=353e958b71e5f8b5e61e57ddcafb983c");
        return new JsonResponse($value);
    }

    /**
     * @Route("/add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request) //add new city to weather city list
    {
        if ($request->request->get('add')) {
            $city = new City();
            $session = $request->getSession();
            $city->setName($session->get('city'));
            $city->setCode($session->get('code'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($city);
            $em->flush();
            return $this->redirectToRoute('adminPanel');
        }

        return $this->render('WeatherBundle:Weather:create.html.twig', array());
    }

    /**
     * @Route("/delete")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request) //delete selected city from weather city list
    {

        if ($request->request->get('cityToDelete')) {
            $em = $this->getDoctrine()->getManager();
            $cityToDelete = $em->getRepository('WeatherBundle:City')->find($request->request->get('cityToDelete'));
            $em->remove($cityToDelete);
            $em->flush();
            return $this->render('WeatherBundle:Weather:adminPanel.html.twig', array(
                'deletedCity' => $cityToDelete
            ));
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
    public function findCityAction(Request $request) //function, thanks to which we don't have to type city code.
    {
        if ($request->request->get('city')) {
            $cityName = $request->request->get('city');
            //delete all polish alphabet special characters and set first character of every words to upper-case
            $cityName = str_replace(array('ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż'), array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z'), $cityName);
            $cityName = strtolower($cityName);
            $cityName = explode(' ', $cityName);
            for ($i = 0; $i != count($cityName); $i++) {
                $cityName[$i] = ucfirst($cityName[$i]);
            }
            $cityName = implode(' ', $cityName);
            $value = file_get_contents("http://openweathermap.org/help/city_list.txt");

            if (strpos($value, $cityName) !== false) {
                // find the city code by city name from web page city list
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
            } else {
                return $this->render('WeatherBundle:Weather:create.html.twig', array(
                    'failure' => 0,
                    'cityName' => $cityName
                ));
            }
        }
    }

    /**
     * @Route("/adminPanel", name="adminPanel")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function adminPanelAction()
    {
        return $this->render('WeatherBundle:Weather:adminPanel.html.twig', array());
    }

    /**
     * @Route("/selectCity", name="selectCity")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function selectCityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cities = $em->getRepository('WeatherBundle:City')->findAll();

        return $this->render('WeatherBundle:Weather:selectCity.html.twig', array(
            'cities' => $cities
        ));
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $id) //edit selected city - name and code
    {
        $em = $this->getDoctrine()->getManager();
        $city = $em->getRepository('WeatherBundle:City')->find($id);
        $form = $this->createForm('WeatherBundle\Form\CityType', $city);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $form->getData();
            $em->persist($city);
            $em->flush();
            return $this->redirectToRoute('adminPanel');
        }

        return $this->render('WeatherBundle:Weather:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
