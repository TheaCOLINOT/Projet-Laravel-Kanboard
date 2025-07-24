<?php

namespace App\Http\Controllers;

use App\Mail\ProjectInvitationMail;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function send(Request $request, Project $project)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // roles <= 3 peuvent inviter
        if (Auth::user()->role > 3) {
            abort(403, 'Vous n\'avez pas la permission d\'inviter des utilisateurs.');
        }

        $email = $request->email;
        $receiver = User::where('email', $email)->first();

        $invitation = Invitation::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver?->id,
            'email' => $email,
            'project_id' => $project->id,
            'status' => 0,
            'token' => Str::random(40),
        ]);

        Mail::to($email)->send(new ProjectInvitationMail($invitation));

        return back()->with('success', 'Invitation envoyée avec succès.');
    }

    public function accept($token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (!$invitation->receiver_id && auth()->check()) {
            $invitation->receiver_id = auth()->id();
        }
        if ($invitation->status != 0) {
            return redirect()->route('home')->with('error', 'Cette invitation a déjà été acceptée ou expirée.');
        }
        if(auth()->user()->projects->contains($invitation->project)) {
            return redirect()->route('projects.show', $invitation->project)->with('info', 'Vous faites déjà partie de ce projet.');
        }

        $invitation->status = 1;
        $invitation->save();

        $project = $invitation->project;
        $project->users()->attach($invitation->receiver_id, ['role' => 2]);

        return redirect()->route('projects.show', $project)->with('success', 'Vous avez rejoint le projet.');
    }
}
