<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import qrCode from '@/routes/qr-code';
import { type BreadcrumbItem } from '@/types';
import { Business } from '@/types/business';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Download,
    Eye,
    FileImage,
    QrCode as QrIcon,
    Upload,
    X,
} from 'lucide-vue-next';
import Editor from 'primevue/editor';
import Slider from 'primevue/slider';
import ToggleSwitch from 'primevue/toggleswitch';
import { ref } from 'vue';

interface Props {
    business: Business;
    qrCodeUrl: string;
    reviewUrl: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'QR Designer', href: qrCode.index().url },
];

const activeMode = ref<'qr' | 'poster'>('qr');

const qrSize = ref(400);
const qrForeground = ref('#000000');
const qrBackground = ref('#ffffff');

const posterBgColor = ref('#f8f9fa');
const posterTextColor = ref('#1a1a1a');
const posterTitle = ref(props.business.name);
const posterDescription = ref(
    `Scan to share your experience at ${props.business.name}!`,
);
const posterFooter = ref('Scan with your camera');
const posterQrSize = ref(600);
const posterBgImage = ref<string | null>(null);
const posterBgImageFile = ref<File | null>(null);

const showLogo = ref(true);
const showTitle = ref(true);
const showDescription = ref(true);
const showFooter = ref(true);

const previewUrl = ref<string | null>(null);
const isGeneratingPreview = ref(false);
const isDownloading = ref(false);

const handleBgImageUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        posterBgImageFile.value = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            posterBgImage.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeBgImage = () => {
    posterBgImage.value = null;
    posterBgImageFile.value = null;
};

const generatePreview = async () => {
    isGeneratingPreview.value = true;

    try {
        if (activeMode.value === 'qr') {
            const response = await axios.post(
                '/qr-code/preview',
                {
                    size: qrSize.value,
                    foreground_color: qrForeground.value,
                    background_color: qrBackground.value,
                    margin: 0,
                },
                {
                    responseType: 'blob',
                },
            );

            const blob = new Blob([response.data], { type: 'image/svg+xml' });
            if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = URL.createObjectURL(blob);
        } else {
            const formData = new FormData();
            formData.append('bg_color', posterBgColor.value);
            formData.append('text_color', posterTextColor.value);
            formData.append('title', posterTitle.value);
            formData.append('description', posterDescription.value);
            formData.append('footer', posterFooter.value);
            formData.append('qr_size', posterQrSize.value.toString());
            formData.append('qr_foreground', qrForeground.value);
            formData.append('qr_background', qrBackground.value);
            formData.append('show_logo', showLogo.value ? '1' : '0');
            formData.append('show_title', showTitle.value ? '1' : '0');
            formData.append(
                'show_description',
                showDescription.value ? '1' : '0',
            );
            formData.append('show_footer', showFooter.value ? '1' : '0');

            if (posterBgImageFile.value) {
                formData.append('bg_image', posterBgImageFile.value);
            }

            const response = await axios.post(
                '/qr-code/preview-poster',
                formData,
                {
                    responseType: 'blob',
                    headers: { 'Content-Type': 'multipart/form-data' },
                },
            );

            const blob = new Blob([response.data], { type: 'application/pdf' });
            if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = URL.createObjectURL(blob);
        }
    } catch (error) {
        console.error('Preview generation failed:', error);
    } finally {
        isGeneratingPreview.value = false;
    }
};

const downloadDesign = async () => {
    isDownloading.value = true;

    try {
        if (activeMode.value === 'qr') {
            const params = new URLSearchParams({
                format: 'png',
                size: qrSize.value.toString(),
                foreground_color: qrForeground.value,
                background_color: qrBackground.value,
                margin: '20',
            });

            window.location.href = `/qr-code/download?${params}`;
        } else {
            const formData = new FormData();
            formData.append('format', 'poster');
            formData.append('bg_color', posterBgColor.value);
            formData.append('text_color', posterTextColor.value);
            formData.append('title', posterTitle.value);
            formData.append('description', posterDescription.value);
            formData.append('footer', posterFooter.value);
            formData.append('qr_size', posterQrSize.value.toString());
            formData.append('qr_foreground', qrForeground.value);
            formData.append('qr_background', qrBackground.value);
            formData.append('show_logo', showLogo.value ? '1' : '0');
            formData.append('show_title', showTitle.value ? '1' : '0');
            formData.append(
                'show_description',
                showDescription.value ? '1' : '0',
            );
            formData.append('show_footer', showFooter.value ? '1' : '0');

            if (posterBgImageFile.value) {
                formData.append('bg_image', posterBgImageFile.value);
            }

            const response = await axios.post('/qr-code/download', formData, {
                responseType: 'blob',
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `${props.business.slug}-poster.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    } finally {
        setTimeout(() => {
            isDownloading.value = false;
        }, 1000);
    }
};

const applyBrandColors = () => {
    qrForeground.value = props.business.brand_color_primary || '#000000';
    posterTextColor.value = props.business.brand_color_primary || '#1a1a1a';
};
</script>

<template>
    <Head title="QR Designer" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">QR Designer</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Create stunning QR codes and posters
                    </p>
                </div>
                <Button @click="applyBrandColors" variant="outline" size="sm">
                    Use Brand Colors
                </Button>
            </div>

            <div class="flex gap-2">
                <Button
                    v-permission="'qr.create'"
                    @click="activeMode = 'qr'"
                    :variant="activeMode === 'qr' ? 'default' : 'outline'"
                    size="sm"
                >
                    <QrIcon class="mr-2 h-4 w-4" />
                    QR Code
                </Button>
                <Button
                    v-permission="'qr.poster-create'"
                    @click="activeMode = 'poster'"
                    :variant="activeMode === 'poster' ? 'default' : 'outline'"
                    size="sm"
                >
                    <FileImage class="mr-2 h-4 w-4" />
                    Poster
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[400px_1fr]">
                <div class="space-y-4">
                    <Card v-if="activeMode === 'qr'">
                        <CardHeader>
                            <CardTitle class="text-lg"
                                >QR Code Settings</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label>Size: {{ qrSize }}px</Label>
                                <Slider
                                    v-model="qrSize"
                                    :min="200"
                                    :max="800"
                                    :step="50"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-2">
                                    <Label>Foreground</Label>
                                    <div class="flex gap-2">
                                        <Input
                                            v-model="qrForeground"
                                            type="color"
                                            class="h-9 w-12"
                                        />
                                        <Input
                                            v-model="qrForeground"
                                            class="flex-1 font-mono text-xs"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>Background</Label>
                                    <div class="flex gap-2">
                                        <Input
                                            v-model="qrBackground"
                                            type="color"
                                            class="h-9 w-12"
                                        />
                                        <Input
                                            v-model="qrBackground"
                                            class="flex-1 font-mono text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div v-else class="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg"
                                    >Poster Design</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <Label>Background</Label>
                                        <div class="flex gap-2">
                                            <Input
                                                v-model="posterBgColor"
                                                type="color"
                                                class="h-9 w-12"
                                            />
                                            <Input
                                                v-model="posterBgColor"
                                                class="flex-1 font-mono text-xs"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Text Color</Label>
                                        <div class="flex gap-2">
                                            <Input
                                                v-model="posterTextColor"
                                                type="color"
                                                class="h-9 w-12"
                                            />
                                            <Input
                                                v-model="posterTextColor"
                                                class="flex-1 font-mono text-xs"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>Background Image</Label>
                                    <div v-if="posterBgImage" class="relative">
                                        <img
                                            :src="posterBgImage"
                                            class="h-32 w-full rounded-lg object-cover"
                                        />
                                        <Button
                                            @click="removeBgImage"
                                            size="icon"
                                            variant="destructive"
                                            class="absolute top-2 right-2 h-6 w-6"
                                        >
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                    <div v-else>
                                        <label
                                            class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed hover:bg-accent"
                                        >
                                            <Upload
                                                class="mb-2 h-8 w-8 text-muted-foreground"
                                            />
                                            <span
                                                class="text-xs text-muted-foreground"
                                                >Upload Image</span
                                            >
                                            <input
                                                type="file"
                                                class="hidden"
                                                @change="handleBgImageUpload"
                                                accept="image/*"
                                            />
                                        </label>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg">Content</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <Label>Show Logo</Label>
                                    <ToggleSwitch v-model="showLogo" />
                                </div>

                                <Separator />

                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Label>Title</Label>
                                        <ToggleSwitch v-model="showTitle" />
                                    </div>
                                    <Input
                                        v-if="showTitle"
                                        v-model="posterTitle"
                                    />
                                </div>

                                <Separator />

                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Label>Description</Label>
                                        <ToggleSwitch
                                            v-model="showDescription"
                                        />
                                    </div>
                                    <Editor
                                        v-if="showDescription"
                                        v-model="posterDescription"
                                        editorStyle="height: 120px"
                                    />
                                </div>

                                <Separator />

                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Label>Footer</Label>
                                        <ToggleSwitch v-model="showFooter" />
                                    </div>
                                    <Input
                                        v-if="showFooter"
                                        v-model="posterFooter"
                                    />
                                </div>

                                <Separator />

                                <div class="space-y-2">
                                    <Label
                                        >QR Code Size:
                                        {{ posterQrSize }}px</Label
                                    >
                                    <Slider
                                        v-model="posterQrSize"
                                        :min="400"
                                        :max="1000"
                                        :step="50"
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg"
                                    >QR Code Colors</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <Label>Foreground</Label>
                                        <div class="flex gap-2">
                                            <Input
                                                v-model="qrForeground"
                                                type="color"
                                                class="h-9 w-12"
                                            />
                                            <Input
                                                v-model="qrForeground"
                                                class="flex-1 font-mono text-xs"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Background</Label>
                                        <div class="flex gap-2">
                                            <Input
                                                v-model="qrBackground"
                                                type="color"
                                                class="h-9 w-12"
                                            />
                                            <Input
                                                v-model="qrBackground"
                                                class="flex-1 font-mono text-xs"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <Button
                            @click="generatePreview"
                            :disabled="isGeneratingPreview"
                            class="w-full"
                        >
                            <Eye class="mr-2 h-4 w-4" />
                            Preview
                        </Button>
                        <Button
                            @click="downloadDesign"
                            :disabled="isDownloading"
                            variant="outline"
                            class="w-full"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Download
                        </Button>
                    </div>
                </div>

                <Card class="flex-1">
                    <CardContent
                        class="flex h-full items-center justify-center bg-muted/30 p-6"
                    >
                        <div
                            v-if="!previewUrl"
                            class="text-center text-muted-foreground"
                        >
                            <Eye class="mx-auto mb-3 h-12 w-12 opacity-20" />
                            <p class="text-sm">
                                Click Preview to see your design
                            </p>
                        </div>
                        <div
                            v-else-if="activeMode === 'qr'"
                            class="flex items-center justify-center p-8"
                        >
                            <img
                                :src="previewUrl"
                                class="max-h-[600px] max-w-full rounded-lg bg-white p-4 shadow-lg"
                            />
                        </div>
                        <iframe
                            v-else
                            :src="previewUrl"
                            class="h-[800px] w-full rounded-lg border-0 shadow-lg"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
