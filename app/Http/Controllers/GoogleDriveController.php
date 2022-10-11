<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class GoogleDriveController extends Controller
{
    public $gClient;
    public function __construct(){
        $google_redirect_url = url('google/login');
        $this->gClient = new \Google_Client();
        $this->gClient->setApplicationName('Demo App');
        $this->gClient->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->gClient->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->gClient->setRedirectUri($google_redirect_url);
        $this->gClient->setDeveloperKey(env('GOOGLE_API_KEY'));
        $this->gClient->setScopes(array(               
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive',
            // 'https://www.googleapis.com/auth/userinfo.email',
            // 'https://www.googleapis.com/auth/userinfo.profile'
        ));
        $this->gClient->setAccessType("offline");
        $this->gClient->setApprovalPrompt("force");
    }

    public function index()
    {
        return view('drive');
    }

    public function login(Request $request)  {
        
        $google_oauthV2 = new \Google_Service_Oauth2($this->gClient);
        if ($request->get('code')){
            $this->gClient->authenticate($request->get('code'));
            $request->session()->put('token', $this->gClient->getAccessToken());
        }
        if ($request->session()->get('token'))
        {
            $this->gClient->setAccessToken($request->session()->get('token'));
        }
        if ($this->gClient->getAccessToken())
        {
            //For logged in user, get details from google using acces
            $user = User::find(1);
            $user->access_token = json_encode($request->session()->get('token'));
            $user->save();
            dd("Successfully Authenticated");
        } else
        {
            //For Guest user, get google login url
            $authUrl = $this->gClient->createAuthUrl();
            return redirect()->to($authUrl);
        }
    }
    public function upload(Request $request){
        $service = new \Google_Service_Drive($this->gClient);
        $user = User::find(1);
        $this->gClient->setAccessToken(json_decode($user->access_token,true));

        if ($this->gClient->isAccessTokenExpired()){
            // save refresh token to some variable
            $refreshTokenSaved = $this->gClient->getRefreshToken();
            // update access token
            $this->gClient->fetchAccessTokenWithRefreshToken($refreshTokenSaved);               
            // // pass access token to some variable
            $updatedAccessToken = $this->gClient->getAccessToken();
            // // append refresh token
            $updatedAccessToken['refresh_token'] = $refreshTokenSaved;
            //Set the new acces token
            $this->gClient->setAccessToken($updatedAccessToken);
            
            $user->access_token=$updatedAccessToken;
            $user->save();                
        }


        $folderName = 'Images';
        $mainfolderId = '1ejOHGDDKBoyTZH4-g-c_S7vWO-u2iEh4';
        $folders = $service->files->listFiles(array("q" => "name='{$folderName}' and '{$mainfolderId}' in parents and mimeType='application/vnd.google-apps.folder'"));
        
        if (count($folders->getFiles()) == 0) {
            $f = new \Google_Service_Drive_DriveFile();
            $f->setName($folderName);
            $f->setMimeType('application/vnd.google-apps.folder');
            $f->setParents(array($mainfolderId));
            $folderId = $service->files->create($f)->getId();
        }else {
            $folderId = $folders->getFiles()[0]->getId();
        }
        
        $file = new \Google_Service_Drive_DriveFile(array(
            'name' => '1.jpg',
            'parents' => [$folderId],
        ));

        $result = $service->files->create($file, array(
          // 'data' => file_get_contents(public_path('google-drive/1.jpg')),
          'data' => file_get_contents($request->file('file')),
          'mimeType' => 'application/octet-stream',
          'uploadType' => 'media'
        ));

        // get url of uploaded file
        $url='https://drive.google.com/open?id='.$result->id;
        dd($url);
    }
}
