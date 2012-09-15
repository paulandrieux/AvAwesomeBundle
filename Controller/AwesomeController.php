<?php

namespace AppVentus\AwesomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Leny BERNARD <leny@appventus.com>
 **/
abstract class AwesomeController extends BaseController
{
    public function dispatchEvent($eventName, Event $event = null)
    {
        $this->get('event_dispatcher')->dispatch($eventName, $event);
    }

    public function getUser()
    {
        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function isGranted($attributes, $object = null)
    {
        return $this->get('security.context')->isGranted($attributes, $object);
    }

    public function setFlash($name, $message)
    {
        $this->get('session')->setFlash($name, $message);
    }

    public function getSession($name, $default = null)
    {
        return $this->get('session')->get($name, $default);
    }

    public function setSession($name, $value)
    {
        $this->get('session')->set($name, $value);
    }

    public function persistAndFlush($entity)
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function removeAndFlush($entity)
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    public function createAndSendMail($subject, $from, $to, $body, $contentType = null)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body, $contentType)
            ;

        $this->get('mailer')->send($message);
    }

    public function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    public function isGrantedOr403($attributes, $object = null, $message = null)
    {
        if ($this->get('security.context')->isGranted($attributes, $object)) {
            return;
        }

        throw $this->createAccessDeniedException($message);
    }

    public function getCurrentUserOr403()
    {
        $user = $this->getUser();

        if (null === $user) {
            throw $this->createAccessDeniedException('This user does not have access to this section.');
        }

        return $user;
    }

    public function redirectReferer()
    {
        $url = $this->container->get('request')->headers->get('referer');
        if (empty($url)) {
            $url = $this->container->get('router')->generate('homepage');
        }

        return new RedirectResponse($url);
    }

    public function isReferer($url)
    {
        return $url === $this->container->get('request')->headers->get('referer');
    }

    public function createJsonResponse($data, $status = 200)
    {
        return new Response(
            json_encode($data),
            $status,
            array('content-type' => 'application/json')
        );
    }

    public function findEntityOr404($entity, $criteria) {
        if (method_exists($this, 'get'.$entity.'Repository')) {
            $obj = $this->{'get'.$entity.'Repository'}()->findOneBy($criteria);
        } else {
            throw new \BadMethodCallException(
                'Undefined method "get' . $entity . 'Repository". Please ' .
                'make sure both method and entity exist.'
            );
        }

        if (null === $obj) {
            throw $this->createNotFoundException(sprintf(
                '%s with parameter(s) %s couldn\'t be found',
                $entity,
                http_build_query($criteria)
            ));
        }

        return $obj;
    }

    public function createAccessDeniedException($message = 'Access Denied', \Exception $previous = null)
    {
        return new AccessDeniedException($message, $previous);
    }
    public function getAdRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->container->getParameter('final_namespace_entity').'Ad');
    }
    public function getSectorRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->container->getParameter('final_namespace_entity').'Sector');
    }
    public function getUserRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->container->getParameter('final_namespace_entity').'User');
    }
    public function getCandidateRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->container->getParameter('final_namespace_entity').'Candidate');
    }
    public function getApplicantRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->container->getParameter('final_namespace_entity').'Applicant');
    }
}
