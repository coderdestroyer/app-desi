@extends('partials.layouts.user')

@section('title','Profil Saya')

@section('content')

<div class="card">

    <div class="header">

        <div>

            <span class="badge">

                <i class="fa-solid fa-user"></i>

                Profil Saya

            </span>

            <h1>
                Informasi <span>Profil</span>
            </h1>

        </div>

        <div class="date">

            {{ now()->locale('id')->translatedFormat('d F Y') }}

            <small>Tanggal Sistem</small>

        </div>

    </div>

    <div class="table-profile">

        <div class="row">

            <div class="left">
                <i class="fa-solid fa-user"></i>
                Nama Lengkap
            </div>

            <div class="right">
                {{ $user->name }}
            </div>

        </div>

        <div class="row">

            <div class="left">
                <i class="fa-solid fa-envelope"></i>
                Alamat Email
            </div>

            <div class="right">
                {{ $user->email }}
            </div>

        </div>

        <div class="row">

            <div class="left">
                <i class="fa-solid fa-user-tag"></i>
                Role
            </div>

            <div class="right">
                {{ ucfirst($user->role) }}
            </div>

        </div>

        <div class="row">

            <div class="left">
                <i class="fa-solid fa-circle-check"></i>
                Status Email
            </div>

            <div class="right">

                @if($user->email_verified_at)

                    <span class="verified">

                        Terverifikasi

                    </span>

                @else

                    <span class="notverified">

                        Belum Verifikasi

                    </span>

                @endif

            </div>

        </div>

        <div class="row">

            <div class="left">
                <i class="fa-solid fa-calendar-days"></i>
                Bergabung Sejak
            </div>

            <div class="right">
                {{ $user->created_at->translatedFormat('d F Y') }}
            </div>

        </div>

    </div>

</div>

@endsection