<?php
namespace Concrete\Authentication\PhpGridServer;

use Concrete\Core\Authentication\AuthenticationTypeController;
use Config;
use Exception;
use Loader;
use User;
use UserInfo;
use View;

require_once("lib/services.php");

class Controller extends AuthenticationTypeController
{
    public $apiMethods = array('forgot_password', 'change_password', 'password_changed', 'invalid_token');
    private $lastToken = "";

    public function getHandle()
    {
        return 'phpgridserver';
    }
    
    public function deauthenticate(User $u)
    {
        list($uID, $authType, $hash) = explode(':', $_COOKIE['ccmAuthUserHash']);
        if ($authType == 'phpgridserver') {
            $authInfoService = getService("AuthInfo");
            $tk = explode("+", $hash);
            $authInfoService->releaseToken($tk[0], $tk[1]);
        }
    }

    public function getAuthenticationTypeIconHTML()
    {
        return '<i class="fa fa-user"></i>';
    }


    public function verifyHash(User $u, $hash)
    {
        $uID = $u->getUserID();
        $authInfoService = getService("AuthInfo");
        $tk = explode("+", $hash);
        try
        {
            $authInfoService->verifyToken($tk[0], $tk[1], 14 * 24 * 3600);
            return true;
        }
        catch(Exception $e)
        {
            trigger_error($e->Message);
            return false;
        }
    }

    public function view()
    {
    }

    public function buildHash(User $u, $test = 1)
    {
        return $this->lastToken;
    }

    public function isAuthenticated(User $u)
    {
        return ($u->isLoggedIn());
    }


    public function saveAuthenticationType($values)
    {
    }


    public function forgot_password()
    {
        $loginData['success'] = 0;
        $error = Loader::helper('validation/error');
        $vs = Loader::helper('validation/strings');
        $em = $this->post('uEmail');

        if ($em) {
            try {
                if (!$vs->email($em)) {
                    throw new \Exception(t('Invalid email address.'));
                }

                $oUser = UserInfo::getByEmail($em);
                if (!$oUser) {
                    throw new \Exception(t('We have no record of that email address.'));
                }

                $mh = Loader::helper('mail');
                //$mh->addParameter('uPassword', $oUser->resetUserPassword());
                $mh->addParameter('uName', $oUser->getUserName());
                $mh->to($oUser->getUserEmail());

                //generate hash that'll be used to authenticate user, allowing them to change their password
                $h = new \Concrete\Core\User\ValidationHash;
                $uHash = $h->add($oUser->uID, intval(UVTYPE_CHANGE_PASSWORD), true);
                $changePassURL = BASE_URL . View::url(
                        '/login',
                        'callback',
                        $this->getAuthenticationType()->getAuthenticationTypeHandle(),
                        'change_password',
                        $uHash);

                $mh->addParameter('changePassURL', $changePassURL);

                if (defined('EMAIL_ADDRESS_FORGOT_PASSWORD')) {
                    $mh->from(EMAIL_ADDRESS_FORGOT_PASSWORD, t('Forgot Password'));
                } else {
                    $adminUser = UserInfo::getByID(USER_SUPER_ID);
                    if (is_object($adminUser)) {
                        $mh->from($adminUser->getUserEmail(), t('Forgot Password'));
                    }
                }

                $mh->addParameter('siteName', Config::get('concrete.site'));
                $mh->load('forgot_password');
                @$mh->sendMail();

            } catch (\Exception $e) {
                $error->add($e);
            }

            $this->redirect('/login', $this->getAuthenticationType()->getAuthenticationTypeHandle(), 'password_sent');
        } else {
            $this->set('authType', $this->getAuthenticationType());
        }
    }

    public function change_password($uHash = '')
    {
        $this->set('authType', $this->getAuthenticationType());
        $db = Loader::db();
        $h = Loader::helper('validation/identifier');
        $e = Loader::helper('validation/error');
        $ui = UserInfo::getByValidationHash($uHash);
        if (is_object($ui)) {
            $hashCreated = $db->GetOne("SELECT uDateGenerated FROM UserValidationHashes WHERE uHash=?", array($uHash));
            if ($hashCreated < (time() - (USER_CHANGE_PASSWORD_URL_LIFETIME))) {
                $h->deleteKey('UserValidationHashes', 'uHash', $uHash);
                throw new \Exception(
                    t(
                        'Key Expired. Please visit the forgot password page again to have a new key generated.'));
            } else {

                if (strlen($_POST['uPassword'])) {

                    $userHelper = Loader::helper('concrete/user');
                    $userHelper->validNewPassword($_POST['uPassword'], $e);

                    if (strlen($_POST['uPassword']) && $_POST['uPasswordConfirm'] != $_POST['uPassword']) {
                        $e->add(t('The two passwords provided do not match.'));
                    }

                    if (!$e->has()) {
                        $authInfoService = getService("AuthInfo");
                        $principalID = $this->getBoundUserBinding($ui->getUserID());
                        $authInfo = $authInfoService->getAuthInfo($principalID);
                        $authInfo->Password = $_POST['uPassword'];
                        $authInfoService->setAuthInfo($authInfo);
                        $h->deleteKey('UserValidationHashes', 'uHash', $uHash);
                        $this->set('passwordChanged', true);

                        $this->redirect(
                            '/login',
                            $this->getAuthenticationType()->getAuthenticationTypeHandle(),
                            'password_changed');
                    } else {
                        $this->set('uHash', $uHash);
                        $this->set('authTypeElement', 'change_password');
                        $this->set('error', $e);
                    }
                } else {
                    $this->set('uHash', $uHash);
                    $this->set('authTypeElement', 'change_password');
                }
            }
        } else {
            throw new \Exception(
                t(
                    'Invalid Key. Please visit the forgot password page again to have a new key generated.'));
        }
    }

    public function password_changed()
    {
        $this->view();
    }

    public function email_validated()
    {
        $this->view();
    }

    public function invalid_token()
    {
        $this->view();
    }

    public function authenticate()
    {
        $post = $this->post();

        if (!isset($post['uFirstName']) || !isset($post['uLastName']) || !isset($post['uPassword'])) {
            throw new Exception(t('Please provide both username and password.'));
        }
        $uFirstName = $post['uFirstName'];
        $uLastName = $post['uLastName'];
        $uName = $uFirstName.".".$uLastName;
        $uPassword = $post['uPassword'];
        $userAccountService = getService("UserAccount");
        $account = $userAccountService->getAccountByName(null, $uFirstName, $uLastName);
        try
        {
            $userid = $this->getBoundUserID("".$account->ID);
	    if($userid == "")
	    {
		throw new Exception();
	    }
        }
        catch(\Exception $e)
        {
            $data = array();
            $data['uName'] = $uName;
            $data['uPassword'] = "";
            $data['uEmail'] = $account->Email;
            $data['uIsValidated'] = 1;
	    
            $user = \UserInfo::add($data);
            $key = \UserAttributeKey::getByHandle('first_name');
            if ($key) {
                $user->setAttribute($key, $uFirstName);
            }

            $key = \UserAttributeKey::getByHandle('last_name');
            if ($key) {
                $user->setAttribute($key, $uLastName);
            }
            $this->bindUserID(intval($user->getUserID(), 10), "".$account->ID);
            $userid = $user->getUserID();
        }
        $authenticationService = getService("Authentication");
        $this->lastToken = $account->ID."+".$authenticationService->authenticate($account->ID, md5($uPassword), 14 * 24 * 3600);
        $user = \User::loginByUserID($userid);
        if ($user && !$user->isError()) {
            if ($post['uMaintainLogin']) {
                $user->setAuthTypeCookie('phpgridserver');
            }
            return $user;
        }
        trigger_error("Login error for $uFirstName $uLastName as $userid");
        return null;
    }

    public function getBoundUserID($binding)
    {
        $result = \Database::connection()->executeQuery(
            'SELECT user_id FROM PhpGridServerUserMap WHERE binding LIKE ?',
            array(
                $binding
            ));

        return $result->fetchColumn();
    }

    public function getBoundUserBinding($uID)
    {
        $result = \Database::connection()->executeQuery(
            'SELECT user_id FROM PhpGridServerUserMap WHERE user_id LIKE ?',
            array(
                $uID
            ));

        return $result->fetchColumn();
    }

    public function bindUserID($user_id, $binding)
    {

        if (!$binding || !$user_id) {
            return null;
        }
        $qb = \Database::connection()->createQueryBuilder();

        $or = $qb->expr()->orX();
        $or->add($qb->expr()->eq('user_id', intval($user_id, 10)));
        $or->add($qb->expr()->eq('binding', ':binding'));

        $qb->delete('PhpGridServerUserMap')->where($or)
           ->setParameter(':binding', $binding)
           ->execute();

        return \Database::connection()->insert(
            'PhpGridServerUserMap',
            array(
                'user_id'   => $user_id,
                'binding'   => $binding
            ));
    }
}
