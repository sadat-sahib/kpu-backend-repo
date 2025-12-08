<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\BannerRequest;
use App\Http\Resources\BannerResource;
use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        return BannerResource::collection($banners);
    }

    public function store(BannerRequest $request)
    {
        $banner = Banner::create([
            "title" => $request->title,
            "description" => $request->description,
        ]);

        $path = $request->file('banner')->store('images/banners', 'public');
        $path = "storage/" . $path;
        $banner->image()->create([
            "image" => $path
        ]);

        return response()->json(['message' => "بنر با موفقیت اضافه شد"]);
    }




    public function update(BannerRequest $request, string $id)
    {

        $banner = Banner::findOrFail($id);
        $banner->title = $request->title;
        $banner->description = $request->description;
        $banner->save();

        if ($request->hasFile('image')) {
            $orgPath = explode("/", $banner->image->image);
            array_shift($orgPath);
            $orgPath = implode("/", $orgPath);
            if (Storage::disk('public')->exists($orgPath)) {
                Storage::disk('public')->delete($orgPath);
            }
            $path = $request->file('image')->store('images/banners', 'public');
            $path = $path = "storage/" . $path;
            $banner->image()->update([
                'image' => $path
            ]);
        }
        return response()->json(['message' => 'بنر با موفقیت اپدیت شد']);
    }


    public function destroy(string $id)
    {
        Banner::findOrFail($id)->delete();
        return response()->json(['message' => 'بنر با موفقیت پاک شد']);
    }
}
