<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface Image {
    id: number;
    title: string | null;
    description: string | null;
    filename: string;
    original_name: string | null;
    mime_type: string;
    size_bytes: number;
    url: string;
    created_at: string;
    updated_at: string;
}

interface PaginatedImages {
    data: Image[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Image Gallery',
        href: '/gallery',
    },
];

const images = ref<Image[]>([]);
const loading = ref(false);
const uploading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const totalImages = ref(0);

// Upload form
const uploadForm = ref({
    image: null as File | null,
    title: '',
    description: '',
});

// Edit modal
const showEditModal = ref(false);
const editingImage = ref<Image | null>(null);
const editForm = ref({
    title: '',
    description: '',
});

// Delete confirmation
const showDeleteModal = ref(false);
const deletingImage = ref<Image | null>(null);

const fetchImages = async (page = 1) => {
    loading.value = true;
    try {
        const response = await fetch(`/api/images?page=${page}`);
        const data: PaginatedImages = await response.json();
        images.value = data.data;
        currentPage.value = data.current_page;
        totalPages.value = data.last_page;
        totalImages.value = data.total;
    } catch (error) {
        console.error('Error fetching images:', error);
    } finally {
        loading.value = false;
    }
};

const uploadImage = async () => {
    if (!uploadForm.value.image) return;
    
    uploading.value = true;
    const formData = new FormData();
    formData.append('image', uploadForm.value.image);
    if (uploadForm.value.title) formData.append('title', uploadForm.value.title);
    if (uploadForm.value.description) formData.append('description', uploadForm.value.description);

    try {
        const response = await fetch('/api/images', {
            method: 'POST',
            body: formData,
        });
        
        if (response.ok) {
            uploadForm.value = { image: null, title: '', description: '' };
            await fetchImages(currentPage.value);
        } else {
            alert('Error uploading image');
        }
    } catch (error) {
        console.error('Error uploading image:', error);
        alert('Error uploading image');
    } finally {
        uploading.value = false;
    }
};

const openEditModal = (image: Image) => {
    editingImage.value = image;
    editForm.value = {
        title: image.title || '',
        description: image.description || '',
    };
    showEditModal.value = true;
};

const updateImage = async () => {
    if (!editingImage.value) return;

    try {
        const response = await fetch(`/api/images/${editingImage.value.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(editForm.value),
        });

        if (response.ok) {
            showEditModal.value = false;
            await fetchImages(currentPage.value);
        } else {
            alert('Error updating image');
        }
    } catch (error) {
        console.error('Error updating image:', error);
        alert('Error updating image');
    }
};

const openDeleteModal = (image: Image) => {
    deletingImage.value = image;
    showDeleteModal.value = true;
};

const deleteImage = async () => {
    if (!deletingImage.value) return;

    try {
        const response = await fetch(`/api/images/${deletingImage.value.id}`, {
            method: 'DELETE',
        });

        if (response.ok) {
            showDeleteModal.value = false;
            await fetchImages(currentPage.value);
        } else {
            alert('Error deleting image');
        }
    } catch (error) {
        console.error('Error deleting image:', error);
        alert('Error deleting image');
    }
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        uploadForm.value.image = target.files[0];
    }
};

onMounted(() => {
    fetchImages();
});
</script>

<template>
    <Head title="Image Gallery" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Image Gallery</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage your image collection</p>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ totalImages }} images total
                </div>
            </div>

            <!-- Upload Form -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Upload New Image</h2>
                <form @submit.prevent="uploadImage" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Image File
                        </label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleFileChange"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title (optional)
                            </label>
                            <input
                                v-model="uploadForm.title"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter image title"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description (optional)
                            </label>
                            <input
                                v-model="uploadForm.description"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter image description"
                            />
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="uploading || !uploadForm.image"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="uploading">Uploading...</span>
                        <span v-else>Upload Image</span>
                    </button>
                </form>
            </div>

            <!-- Images Grid -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <div v-if="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Loading images...</p>
                </div>
                
                <div v-else-if="images.length === 0" class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No images found. Upload your first image above!</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="image in images"
                        :key="image.id"
                        class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <!-- Image -->
                        <div class="aspect-square overflow-hidden">
                            <img
                                :src="image.url"
                                :alt="image.title || image.original_name || 'Image'"
                                class="h-full w-full object-cover transition-transform group-hover:scale-105"
                            />
                        </div>

                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 transition-all group-hover:bg-opacity-30">
                            <div class="absolute bottom-0 left-0 right-0 transform translate-y-full transition-transform group-hover:translate-y-0 bg-gradient-to-t from-black to-transparent p-4">
                                <div class="flex space-x-2">
                                    <button
                                        @click="openEditModal(image)"
                                        class="flex-1 rounded bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="openDeleteModal(image)"
                                        class="flex-1 rounded bg-red-600 px-3 py-1 text-xs text-white hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                {{ image.title || image.original_name || 'Untitled' }}
                            </h3>
                            <p v-if="image.description" class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                {{ image.description }}
                            </p>
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ formatFileSize(image.size_bytes) }}</span>
                                <span>{{ new Date(image.created_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="mt-6 flex items-center justify-center space-x-2">
                    <button
                        @click="fetchImages(currentPage - 1)"
                        :disabled="currentPage <= 1"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Previous
                    </button>
                    <span class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button
                        @click="fetchImages(currentPage + 1)"
                        :disabled="currentPage >= totalPages"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showEditModal = false"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Edit Image</h3>
                <form @submit.prevent="updateImage" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title
                        </label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                        >
                            Update
                        </button>
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showDeleteModal = false"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Delete Image</h3>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this image? This action cannot be undone.
                </p>
                <div class="flex space-x-3">
                    <button
                        @click="deleteImage"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                    >
                        Delete
                    </button>
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
