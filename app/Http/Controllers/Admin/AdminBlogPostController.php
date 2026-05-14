<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminBlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $posts = BlogPost::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blogs.index', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.blogs.form', [
            'post' => new BlogPost(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        BlogPost::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Post criado com sucesso.');
    }

    public function edit(BlogPost $blog): View
    {
        return view('admin.blogs.form', [
            'post' => $blog,
        ]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $previousImage = $blog->image;
        $data = $this->validated($request, $blog->id, $blog);
        $blog->update($data);

        if (($data['image'] ?? null) !== $previousImage) {
            $this->deleteStoredImage($previousImage);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Post atualizado com sucesso.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        $this->deleteStoredImage($blog->image);
        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Post removido com sucesso.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('image');
        $path = $this->optimizeAndStoreImage($file, 'blog/editor');

        return response()->json([
            'url' => $path,
        ]);
    }

    private function validated(Request $request, ?int $ignoreId = null, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'excerpt' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image_file' => array_values(array_filter([
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'max:5120',
                $ignoreId === null ? 'required' : null,
            ])),
            'category' => ['required', 'string', 'max:120'],
            'active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image_file')) {
            /** @var UploadedFile $coverImage */
            $coverImage = $request->file('image_file');
            $data['image'] = $this->optimizeAndStoreImage($coverImage, 'blog/covers');
        } elseif ($post?->image) {
            $data['image'] = $post->image;
        }

        $data['slug'] = $this->generateUniqueSlug((string) $data['title'], $ignoreId);
        $data['read_time'] = $this->calculateReadTime((string) $data['content']);
        $data['published_at'] = $ignoreId === null
            ? now()
            : ($post?->published_at ?? now());
        $data['active'] = $request->boolean('active', true);

        unset($data['image_file']);

        return $data;
    }

    private function calculateReadTime(string $content): string
    {
        $plainText = strip_tags(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plainText = trim((string) preg_replace('/\s+/u', ' ', $plainText));

        $wordCount = str_word_count(
            mb_strtolower($plainText),
            0,
            'àáâãäåçèéêëìíîïñòóôõöùúûüýÿ'
        );

        $charCount = mb_strlen((string) preg_replace('/\s+/u', '', $plainText));
        $minutesByWords = $wordCount > 0 ? (int) ceil($wordCount / 220) : 0;
        $minutesByChars = $charCount > 0 ? (int) ceil($charCount / 1200) : 0;
        $minutes = max(1, $minutesByWords, $minutesByChars);

        return $minutes . ' min de leitura';
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'post';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            BlogPost::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function optimizeAndStoreImage(UploadedFile $file, string $directory): string
    {
        $rawContents = file_get_contents($file->getRealPath());

        if ($rawContents === false || ! function_exists('imagecreatefromstring')) {
            $storedPath = $file->store($directory, 'public');

            return $this->publicImageUrl($storedPath);
        }

        $sourceImage = @imagecreatefromstring($rawContents);

        if ($sourceImage === false) {
            $storedPath = $file->store($directory, 'public');

            return $this->publicImageUrl($storedPath);
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $maxDimension = 1800;
        $scale = min(1, $maxDimension / max($sourceWidth, $sourceHeight));
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        $filename = trim($directory, '/').'/'.Str::uuid().(function_exists('imagewebp') ? '.webp' : '.jpg');

        ob_start();

        if (function_exists('imagewebp')) {
            imagewebp($targetImage, null, 82);
        } else {
            imagejpeg($targetImage, null, 85);
        }

        $optimizedContents = (string) ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        Storage::disk('public')->put($filename, $optimizedContents);

        return $this->publicImageUrl($filename);
    }

    private function deleteStoredImage(?string $imagePath): void
    {
        if (! filled($imagePath)) {
            return;
        }

        $publicPrefix = '/storage/';
        $normalizedPath = str_starts_with($imagePath, $publicPrefix)
            ? ltrim(Str::after($imagePath, $publicPrefix), '/')
            : ltrim(Str::after($imagePath, '/storage/'), '/');

        if ($normalizedPath !== '' && Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }

    private function publicImageUrl(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }
}
