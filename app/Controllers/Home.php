<?php

namespace App\Controllers;

use App\Models\ModelPlaylist;
use App\Models\MusicModel;
use App\Models\ModelTracks;
use App\Controllers\BaseController;

class Home extends BaseController
{
    private $playlist;
    private $music;
    private $tracks;

    public function __construct()
    {
        $this->playlist = new ModelPlaylist();
        $this->music = new MusicModel();
        $this->tracks = new ModelTracks();
    }
    public function index()
    {
        $data['playlists'] = $this->playlist->findAll();
        $data['music'] = $this->music->findAll();
        return view('player', $data);
    }
    public function create()
    {
        $data = [
            'name' => $this->request->getPost('name')
        ];
        $this->playlist->insert($data);
        return redirect()->to('/player');
    }

    public function playlists($id)
    {
        $playlist = $this->playlist->find($id);

        if ($playlist) {
            $tracks = $this->tracks->where('playlist_id', $id)->findAll();
            $music = [];
            foreach ($tracks as $track) {
                $musicItem = $this->music->find($track['track_id']);
                if ($musicItem) {
                    $music[] = $musicItem;
                }
            }
            $data = [
                'playlist' => $playlist,
                'music' => $music,
                'playlists' => $this->playlist->findAll(),
                'tracks' => $tracks,
            ];
            return view('player', $data);
        } else {
            return redirect()->to('/player');
        }
    }

    public function search()
    {
        $search = $this->request->getGet('title');
        $musicResults = $this->music->like('title', '%' . $search . '%')->findAll();
        $data = [
            'playlists' => $this->playlist->findAll(),
            'music' => $musicResults,
        ];
        return view('player', $data);
    }
    public function add()
    {

        $musicID = $this->request->getPost('musicID');
        $playlistID = $this->request->getPost('playlist');

        $data = [
            'playlist_id' => $playlistID,
            'track_id' => $musicID,
        ];
        $this->tracks->insert($data);
        return redirect()->to('/player');
    }

    public function upload()
    {
        $file = $this->request->getFile('file');
        $title = $this->request->getPost('title');
        $artist = $this->request->getPost('artist');
        $newName = $title . '_' . $artist . '.' . 'mp3';
        $file->move(ROOTPATH . 'public/', $newName);
        $data = [
            'title' => $title,
            'artist' => $artist,
            'file_path' => $newName
        ];
        $this->music->insert($data);
        return redirect()->to('/player');
    }
}
