<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreReviewRequest;
use App\Http\Requests\V1\StoreTemplateRequest;
use App\Http\Requests\V1\UpdateTemplateRequest;
use App\Services\Template\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    protected $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    public function index(Request $request)
    {
        return $this->templateService->getTemplates();
    }

    public function getFeatured(Request $request)
    {
        return $this->templateService->getFeatured();
    }

    public function getTrending(Request $request)
    {
        return $this->templateService->getTrending();
    }

    public function getCategories(Request $request)
    {
        return $this->templateService->getCategories();
    }

    public function search(Request $request)
    {
        return $this->templateService->search($request->input('query'));
    }

    public function getFavorites(Request $request)
    {
        return $this->templateService->getFavorites($request->user()->id);
    }

    public function show(Request $request, $id)
    {
        return $this->templateService->getTemplate($id);
    }

    public function useTemplate(Request $request, $id)
    {
        return $this->templateService->useTemplate($id, $request->user()->id, $request->user()->org_id);
    }

    public function cloneTemplate(Request $request, $id)
    {
        return $this->templateService->cloneTemplate($id, $request->user()->id, $request->user()->org_id);
    }

    public function favoriteTemplate(Request $request, $id)
    {
        return $this->templateService->favoriteTemplate($id, $request->user()->id);
    }

    public function unfavoriteTemplate(Request $request, $id)
    {
        return $this->templateService->unfavoriteTemplate($id, $request->user()->id);
    }

    public function publish(StoreTemplateRequest $request)
    {
        return $this->templateService->publish($request->validated(), $request->user()->id, $request->user()->org_id);
    }

    public function update(UpdateTemplateRequest $request, $id)
    {
        return $this->templateService->updateTemplate($id, $request->validated());
    }

    public function destroy(Request $request, $id)
    {
        return $this->templateService->deleteTemplate($id);
    }

    public function getReviews(Request $request, $id)
    {
        return $this->templateService->getReviews($id);
    }

    public function createReview(StoreReviewRequest $request, $id)
    {
        return $this->templateService->createReview($id, $request->user()->id, $request->input('comment'), $request->input('rating'));
    }

    public function updateReview(StoreReviewRequest $request, $id, $reviewId)
    {
        return $this->templateService->updateReview($id, $reviewId, $request->input('comment'), $request->input('rating'));
    }

    public function getStats(Request $request, $id)
    {
        return $this->templateService->getStats($id);
    }

    public function trackUsage(Request $request, $id)
    {
        return $this->templateService->trackUsage($id);
    }
}
