<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class GoogleDriveController extends Controller
{
    public $client;

    public function __construct(){
        $google_redirect_url = url('google/login');
        $this->client = new \Google_Client();
        $this->client->setApplicationName('BackOffice');
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri($google_redirect_url);
        $this->client->setDeveloperKey(env('GOOGLE_API_KEY'));
        $this->client->setScopes(array(               
            'https://www.googleapis.com/auth/drive.file',
            // 'https://www.googleapis.com/auth/drive',
            // 'https://www.googleapis.com/auth/userinfo.email',
            // 'https://www.googleapis.com/auth/userinfo.profile'
        ));
        $this->client->setAccessType("offline");
        $this->client->setApprovalPrompt("force");
    }

    public function index(){
        return view('drive');
    }

    public function login(Request $request)  {
        $google_oauthV2 = new \Google_Service_Oauth2($this->client);

        if($request->get('code')){
            $this->client->authenticate($request->get('code'));
            $request->session()->put('token', $this->client->getAccessToken());
        }

        if ($request->session()->get('token')){
            $this->client->setAccessToken($request->session()->get('token'));
        }

        if ($this->client->getAccessToken()){
            $user = User::find(1);
            $user->access_token = json_encode($request->session()->get('token'));
            $user->save();
            dd('User Authenticated');
        }else{
            $authUrl = $this->client->createAuthUrl();
            return redirect()->to($authUrl);
        }
    }

    public function upload(Request $request){
        $service = new \Google_Service_Drive($this->client);
        $user = User::find(1);

        $this->client->setAccessToken(json_decode($user->access_token,true));
        if ($this->client->isAccessTokenExpired()){
            $refreshTokenSaved = $this->client->getRefreshToken();
            $this->client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
            $updatedAccessToken = $this->client->getAccessToken();
            $updatedAccessToken['refresh_token'] = $refreshTokenSaved;
            $this->client->setAccessToken($updatedAccessToken);
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
        }else{
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

        $url='https://drive.google.com/open?id='.$result->id;
        dd($url);
    }
}
