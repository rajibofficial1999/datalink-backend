<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountInformation;
use App\Models\Category;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteMultipleDataController extends Controller
{
    public function __invoke(Request $request, $table): JsonResponse
    {
        // Check if table exists
        if (!Schema::hasTable($table)) {
            throw ValidationException::withMessages([
                'errors' => ["Table '{$table}' not found."]
            ]);
        }

        // Validate the request data
        $data = $request->validate([
            'data_ids' => 'required|array',
            'data_ids.*' => ['required', 'numeric', "exists:{$table},id"],
        ]);

        // Get the model class for the table
        $model = $this->getModelByTableName($table);

        // Check if model class exists
        if (!$model) {
            throw ValidationException::withMessages([
                'errors' => ["Model for table '{$table}' not found."]
            ]);
        }

        // Delete associated files or related data
        $this->deleteRelatedData($model, $table, $data['data_ids']);

        // Delete all selected records
        $model::whereIn('id', $data['data_ids'])->delete();

        // Return a response indicating success
        return response()->json([
            'success' => 'Records deleted successfully',
        ], Response::HTTP_OK);
    }

    protected function getModelByTableName(string $tableName): ?string
    {
        return [
            'users' => User::class,
            'categories' => Category::class,
            'domains' => Domain::class,
            'account_information' => AccountInformation::class,
        ][$tableName] ?? null;
    }

    protected function deleteRelatedData(string $model, string $table, array $ids): void
    {
        $items = $model::whereIn('id', $ids)->get();

        foreach ($items as $item) {
            $this->deleteItemFiles($table, $item);
        }
    }

    protected function deleteItemFiles(string $table, $item): void
    {
        $filesToDelete = [];

        switch ($table) {
            case 'domains':
                $filesToDelete[] = $item->screenshot ?? '';
                break;

            case 'users':
                $filesToDelete[] = $item->avatar ?? '';
                break;

            case 'account_information':
                $filesToDelete = [
                    $item->nid_front ?? '',
                    $item->nid_back ?? '',
                    $item->selfie ?? ''
                ];
                break;
        }

        foreach ($filesToDelete as $file) {
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }

        // For account_information, delete older photos via method if available
        if ($table === 'account_information') {
            foreach (['nid_front', 'nid_back', 'selfie'] as $photoField) {
                $item->deleteOlderPhoto($item->{$photoField} ?? '');
            }
        }
    }
}
