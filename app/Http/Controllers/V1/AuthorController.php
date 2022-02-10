<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\createAuthorRequest;
use App\Http\Requests\updateAuthorRequest;
use App\Http\Resources\AuthorCollection;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    public function index()
    {

        $authors = DB::table('authors')->orderBy('name')->paginate(3);

        return (new AuthorCollection($authors));
    }

    public function show(Author $author){

        return response()->json(new AuthorResource($author), Response::HTTP_OK);
    }

    public function store(createAuthorRequest $request){

        $author = Author::create([
            'name' => $request->input('data.attributes.name'),
        ]);

        return response()
            ->json(new AuthorResource($author), Response::HTTP_CREATED)
            ->header('Location', route('authors.show', $author));
    }

    public function update(UpdateAuthorRequest $request, Author $author){

        $author->updateOrFail([
            'name' => $request->input('data.attributes.name'),
        ]);

        return response()
            ->json(new AuthorResource($author), Response::HTTP_OK)
            ->header('Location', route('authors.show', $author));

    }


    public function destroy(Author $author){

        $author->deleteOrFail();

        return response('', Response::HTTP_NO_CONTENT);
    }

}
