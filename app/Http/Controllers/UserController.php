<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller {
    //

    public function storeAvatarForm(Request $request) {
        $incomingFields = $request->validate( [
            'avatar' => [ 'required','image','max:3000'],
        ] );

        // $request->file('avatar')->store('public/avatars');
        $user = auth()->user();
        $fileName = $user->id . '-' . uniqid() . '.jpg';
        $avatarData =  Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/'.$fileName, $avatarData);

        $oldAvatar = $user->avatar;

        $user->avatar = $fileName;
        $user->save();

        if($oldAvatar != '/storage/fallback-avatar.jpg'){
            Storage::delete([str_replace('/storage/', 'public/', $oldAvatar)]);
        }

        return redirect('profile/'.$user->username)->with('success', 'Avatar Updated.');
    }

    public function showAvatarForm(User $user) {
        return view('avatar-form');
    }

    private function getSharedData($user){
        $currentlyFollowing = 0;

        if(auth()->check()){
            // $currentlyFollowing = Follow::where(['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id])->count();
            $currentlyFollowing = DB::table('follows')->where('user_id', auth()->user()->id)->where('followeduser', $user->id)->count();

        }

        View::share('sharedData', [
            'avatar' => $user->avatar,
            'username' => $user->username,
            'postCount' => $user->posts()->count(),
            'followerCount' => $user->followers()->count(),
            'followingCount' => $user->followingTheseUsers()->count(),
            'currentlyFollowing' =>$currentlyFollowing
        ]);
    }

    public function profile(User $user) {
        $this->getSharedData($user);
        return view( 'profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user) {
        $data = [
            'theHTML' => view('profile-post-only', ['posts' => $user->posts()->latest()->get()])->render(),
            'docTitle' => "{$user->username}'s profile"
        ];
        return response()->json($data, 200);
    }

    public function profileFollowers(User $user) {
        $this->getSharedData($user);

        // return $user->followers()->latest()->get();
        return view( 'profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowersRaw(User $user) {

        $data = [
            'theHTML' => view('profile-followers-only', ['followers' => $user->followers()->latest()->get()])->render(),
            'docTitle' => "{$user->username}'s Followers"
        ];
        return response()->json($data, 200);

    }

    public function profileFollowing(User $user) {
        $this->getSharedData($user);
        return view( 'profile-following', ['following' => $user->followingTheseUsers()->latest()->get()]);
    }

    public function profileFollowingRaw(User $user) {
        $data = [
            'theHTML' => view('profile-following-only', ['following' => $user->followingTheseUsers()->latest()->get()])->render(),
            'docTitle' => "Who {$user->username}'s Follows"
        ];
        return response()->json($data, 200);

    }

    public function logout( Request $request ) {
        event( new OurExampleEvent(['username' => auth()->user()->username , 'action' => "logout"])); // Events

        auth()->logout();

        return redirect( '/' )->with('success', 'You are now logged out!!');
    }

    public function ShowCorrectHomePage() {
        if ( auth()->check() ) {
            return view( 'homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(8)]);
        } else {

            // if(Cache::has('postCount')){
            //     $postCount = Cache::get('postCount', Post::count());
            // }else{
            //     sleep(5);
            //     $postCount = Cache::set('postCount', Post::count(), 20); // 20s
            // }

            $postCount = Cache::remember('postCount', 20, function () {
                // sleep(5);
                return Post::count();
            });

            return view( 'homepage', ['postCount' => $postCount ]);
        }
    }

    public function loginApi( Request $request ) {
        $incommingFileds = $request->validate( [
            'username' => [ 'required' ],
            'password' => [ 'required' ]
        ] );

        if(auth()->attempt($incommingFileds)){
            $user = User::where('username', $incommingFileds['username'])->first();
            $token = $user->createToken('outapptoken')->plainTextToken;
            return $token;
        }

        return 'sorry';

    }

    public function login( Request $request ) {
        $incommingFileds = $request->validate( [
            'loginusername' => [ 'required' ],
            'loginpassword' => [ 'required' ]
        ] );

        if ( auth()->attempt( [ 'username' => $incommingFileds[ 'loginusername' ], 'password' => $incommingFileds[ 'loginpassword' ] ] ) ) {
            // return 'Cograss!!!';
            $request->session()->regenerate();
            event( new OurExampleEvent(['username' => auth()->user()->username , 'action' => "login"])); // Events

            return redirect( '/' )->with('success', 'You have successfully logged in!!');
        } else {
            return redirect( '/' )->with('failure', 'Invalid Login.');

            // return 'Ops!!!';
        }

    }

    public function register( Request $request ) {

        $incommingFileds = $request->validate( [
            'username' => [ 'required', 'min:3', 'max:20', 'unique:users,username' ],
            'email' => [ 'required', 'email', 'unique:users,email' ],
            'password' => [ 'required',  'between:8,255', 'confirmed' ],
        ] );

        $incommingFileds[ 'password' ] = bcrypt( $incommingFileds[ 'password' ] );

        $user = User::create( $incommingFileds );

        auth()->login($user);

        // $request->session()->flash( 'success_message', $incommingFileds[ 'username' ].' has been registered!!!' );

        return redirect( '/' )->with('success', 'Thank you for creating an Account.');

    }
}
