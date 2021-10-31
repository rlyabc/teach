<!DOCTYPE html>
<!--
  ~ Copyright 2016 LINE Corporation
  ~
  ~ LINE Corporation licenses this file to you under the Apache License,
  ~ version 2.0 (the "License"); you may not use this file except in compliance
  ~ with the License. You may obtain a copy of the License at:
  ~
  ~   http://www.apache.org/licenses/LICENSE-2.0
  ~
  ~ Unless required by applicable law or agreed to in writing, software
  ~ distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
  ~ WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
  ~ License for the specific language governing permissions and limitations
  ~ under the License.
  -->
<html xmlns:th="http://www.thymeleaf.org">
<head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ URL::asset('css/line-login.css')}}" />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="{{ URL::asset('js/success.js')}}"></script>
    <title>LINE Web Login Success</title>
</head>
<style>
    ul{
        list-style: none;
    }
</style>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="area">
                <div class="center-block profile-margin">
                    <img src="{{isset($idToken['picture'])??$idToken['picture']}}" class="profile-img img-circle" />
                    <h3   >{{$idToken['name']}}</h3>
                    {{--<button type="button" id="verify" class="btn btn-success btn-lg btn-block profile-button">verify</button>--}}
                    {{--<button type="button" id="refreshToken" class="btn btn-success btn-lg btn-block profile-button">refreshToken</button>--}}
                    {{--<button type="button" id="revoke" class="btn btn-success btn-lg btn-block profile-button">revoke</button>--}}
                    <a  href="https://myteachceshi.herokuapp.com/index.html#/login?line_user_id={{$idToken['sub']}}"  class="btn btn-success btn-lg btn-block profile-button">前往绑定用户</a>
                    <div>已经绑定的老师：</div>
                    <ul>
                        @if(!empty($teacherUser))
                        <li>{{$teacherUser['name']}} <a href="https://myteachceshi.herokuapp.com/index.html#/lineLogin?line_user_id={{$idToken['sub']}}&user_type=teacher">使用该用户</a> </li>
                        @endif
                    </ul>
                    <div>已经绑定的学生：</div>
                    <ul>
                        @if(!empty($studentUser))
                            @foreach($studentUser as $user)
                                <li>{{$user['name']}} <a href="https://myteachceshi.herokuapp.com/index.html#/lineLogin?line_user_id={{$idToken['sub']}}&user_type=student">使用该用户</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
