<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chirp from '@/Components/Chirp.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps(['chirps']);

const form = useForm({
    id: null,
    message: '',
    image: null,
});

const editChirp = ref({
    id: null,
    message: '',
    image: null,
});

const dropdownVisible = ref(null);

const toggleDropdown = (chirpId) => {
    dropdownVisible.value = dropdownVisible.value === chirpId ? null : chirpId;
};

const handleFileUpload = (event) => {
    form.image = event.target.files[0];
};

const submit = () => {
    form.post(route('chirps.store'), { onSuccess: () => form.reset() });
};

const showEditModal = (chirp) => {
    editChirp.value.id = chirp.id;
    editChirp.value.message = chirp.message;
    editChirp.value.image = null; // Kosongkan file lama di modal
};

const updateChirp = () => {
    const formData = new FormData();
    formData.append('message', editChirp.value.message); // Kirim pesan
    if (editChirp.value.image) {
        formData.append('image', editChirp.value.image); // Kirim file gambar (jika ada)
    }

    // Pastikan route sesuai dengan endpoint update pada backend
    $inertia.put(route('chirps.update', editChirp.value.id), formData, {
        onSuccess: () => {
            editChirp.value = { id: null, message: '', image: null }; // Reset form edit
            dropdownVisible.value = null; // Tutup dropdown
        },
        onError: (errors) => {
            console.error('Error saat update Chirp:', errors);
        },
    });
};


const cancelEdit = () => {
    // Tutup modal dengan mengatur editChirp.id menjadi null
    editChirp.value.id = null;
};
</script>

<template>
    <Head title="Chirps" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <!-- Form untuk menambahkan Chirp -->
            <form @submit.prevent="submit" class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Post a Chirp</h2>

                <textarea
                    v-model="form.message"
                    placeholder="What's on your mind?"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-4"
                    rows="4"
                ></textarea>

                <!-- Input untuk gambar -->
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Upload Image</label>
                <input
                    type="file"
                    id="image"
                    @change="handleFileUpload"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border file:border-gray-300 file:text-sm file:font-semibold file:bg-gray-100 hover:file:bg-gray-200 mb-4"
                />

                <InputError :message="form.errors.message" class="mt-2" />

                <PrimaryButton class="mt-4">Post Chirp</PrimaryButton>
            </form>

            <!-- Daftar Chirp -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Chirps</h2>
                <div
                    v-for="chirp in chirps"
                    :key="chirp.id"
                    class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0"
                >
                    <div class="flex justify-between items-center">
                        <p class="text-gray-700">{{ chirp.message }}</p>

                        <div class="relative">
                            <button
                                @click="toggleDropdown(chirp.id)"
                                class="text-gray-500 hover:text-gray-700 focus:outline-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-6 h-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zm0 5.25a.75.75 0 110-1.5.75.75 0 010 1.5zm0 5.25a.75.75 0 110-1.5.75.75 0 010 1.5z"
                                    />
                                </svg>
                            </button>

                            <div
                                v-if="dropdownVisible === chirp.id"
                                class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded-lg shadow-md z-10"
                            >
                                <button
                                    @click.prevent="showEditModal(chirp)"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Edit
                                </button>
                                <button
                                    @click.prevent="$inertia.delete(route('chirps.destroy', chirp.id))"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <img
                        v-if="chirp.image"
                        :src="`/storage/${chirp.image}`"
                        alt="Uploaded Image"
                        class="w-full max-w-sm mt-2 rounded-md shadow-md"
                    />
                </div>
            </div>
        </div>

        <!-- Modal Edit Chirp -->
        <div v-if="editChirp.id" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50">
            <div class="bg-white p-6 rounded-lg w-96">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Chirp</h3>

                <textarea
                    v-model="editChirp.message"
                    placeholder="Edit your chirp"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-4"
                    rows="4"
                ></textarea>

                <input
                    type="file"
                    @change="(e) => (editChirp.image = e.target.files[0])"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border file:border-gray-300 file:text-sm file:font-semibold file:bg-gray-100 hover:file:bg-gray-200 mb-4"
                />
                

                <PrimaryButton @click="updateChirp" class="mt-4">Save</PrimaryButton>
                <button @click="cancelEdit" class="mt-4 text-gray-500 hover:text-gray-700">Cancel</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
