<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import qrCode from '@/routes/qr-code';
import { type BreadcrumbItem } from '@/types';
import { Business } from '@/types/business';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Download,
    FileCode,
    FileImage,
    Palette,
    Printer,
    QrCode as QrIcon,
    Sparkles,
    Wand2,
} from 'lucide-vue-next';
import Select from 'primevue/select';
import Slider from 'primevue/slider';
import Textarea from 'primevue/textarea';
import { computed, ref, watch } from 'vue';

// Debounce utility
let debounceTimer: number | null = null;
const debounce = (fn: () => void, delay: number = 500): void => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = window.setTimeout(() => fn(), delay);
};

interface Props {
    business: Business;
    qrCodeUrl: string;
    reviewUrl: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'QR Code Designer',
        href: qrCode.index().url,
    },
];

// QR Code Customization State
const activeTab = ref<'qr' | 'poster'>('qr');
const qrSize = ref(300);
const qrForeground = ref('#000000');
const qrBackground = ref('#ffffff');
const qrMargin = ref(0);

// Poster Customization State
const posterTemplate = ref('modern');
const posterSize = ref('a4');
const posterBgColor = ref('#f3f4f6');
const posterTextColor = ref('#1f2937');
const customText = ref(
    `Scan to share your experience at ${props.business.name}!`,
);
const qrSizeInPoster = ref(400);

// Preview State
const previewUrl = ref(props.qrCodeUrl);
const downloading = ref(false);

const templates = [
    {
        value: 'modern',
        label: 'Modern',
        icon: Sparkles,
        description: 'Clean and contemporary',
        defaultBgColor: '#f3f4f6',
        defaultTextColor: '#1f2937',
    },
    {
        value: 'minimal',
        label: 'Minimal',
        icon: QrIcon,
        description: 'Simple and elegant',
        defaultBgColor: '#ffffff',
        defaultTextColor: '#000000',
    },
    {
        value: 'vibrant',
        label: 'Vibrant',
        icon: Palette,
        description: 'Bold and colorful',
        defaultBgColor: '#8b5cf6',
        defaultTextColor: '#ffffff',
    },
    {
        value: 'elegant',
        label: 'Elegant',
        icon: Wand2,
        description: 'Sophisticated design',
        defaultBgColor: '#1a1a1a',
        defaultTextColor: '#ffffff',
    },
];

const posterSizes = [
    { value: 'a4', label: 'A4 (210×297mm)', description: 'Standard paper' },
    { value: 'a5', label: 'A5 (148×210mm)', description: 'Half A4' },
    { value: 'letter', label: 'Letter (8.5×11")', description: 'US standard' },
    {
        value: 'square',
        label: 'Square (30×30cm)',
        description: 'Perfect square',
    },
    {
        value: 'instagram',
        label: 'Instagram Story',
        description: '1080×1920px',
    },
];

const downloadFormats = computed(() => {
    if (activeTab.value === 'qr') {
        return [
            {
                value: 'svg',
                label: 'SVG',
                icon: FileCode,
                description: 'Scalable vector',
            },
            {
                value: 'png',
                label: 'PNG',
                icon: FileImage,
                description: 'High quality image',
            },
        ];
    }
    return [
        {
            value: 'poster',
            label: 'Poster (PDF)',
            icon: Printer,
            description: 'Ready to print',
        },
    ];
});

// Watch for changes and update preview
watch([qrSize, qrForeground, qrBackground, qrMargin], () => {
    if (activeTab.value === 'qr') {
        debounce(() => updatePreview());
    }
});

watch(
    [
        posterTemplate,
        posterSize,
        posterBgColor,
        posterTextColor,
        customText,
        qrSizeInPoster,
        qrForeground,
        qrBackground,
    ],
    () => {
        if (activeTab.value === 'poster') {
            debounce(() => updatePosterPreview());
        }
    },
);

// Watch for template changes to load default colors
watch(posterTemplate, (newTemplate) => {
    const template = templates.find((t) => t.value === newTemplate);
    if (template) {
        posterBgColor.value = template.defaultBgColor;
        posterTextColor.value = template.defaultTextColor;
    }
});

watch(activeTab, (newTab) => {
    if (newTab === 'poster') {
        updatePosterPreview();
    } else {
        updatePreview();
    }
});

const updatePreview = async () => {
    try {
        const response = await axios.post(
            '/qr-code/preview',
            {
                mode: 'qr',
                size: qrSize.value,
                foreground_color: qrForeground.value,
                background_color: qrBackground.value,
                margin: qrMargin.value,
            },
            {
                responseType: 'blob',
            },
        );

        const blob = new Blob([response.data], { type: 'image/svg+xml' });
        previewUrl.value = URL.createObjectURL(blob);
    } catch (error) {
        console.error('Preview update failed:', error);
    }
};

const updatePosterPreview = async () => {
    try {
        const response = await axios.post(
            '/qr-code/preview-poster',
            {
                template: posterTemplate.value,
                poster_size: posterSize.value,
                custom_text: customText.value,
                qr_size: qrSizeInPoster.value,
                background_color: posterBgColor.value,
                text_color: posterTextColor.value,
                qr_foreground: qrForeground.value,
                qr_background: qrBackground.value,
            },
            {
                responseType: 'blob',
            },
        );

        // Create a blob URL for the PDF
        const blob = new Blob([response.data], { type: 'application/pdf' });
        previewUrl.value = URL.createObjectURL(blob);
    } catch (error) {
        console.error('Poster preview update failed:', error);
    }
};

const downloadFile = async (format: string) => {
    downloading.value = true;

    try {
        const params = new URLSearchParams({
            format,
        });

        if (format === 'poster') {
            params.append('template', posterTemplate.value);
            params.append('poster_size', posterSize.value);
            params.append('custom_text', customText.value);
            params.append('qr_size', qrSizeInPoster.value.toString());
            params.append('background_color', posterBgColor.value);
            params.append('text_color', posterTextColor.value);
            params.append('qr_foreground', qrForeground.value);
            params.append('qr_background', qrBackground.value);
        } else {
            params.append(
                'size',
                activeTab.value === 'qr' ? qrSize.value.toString() : '1000',
            );
            params.append('foreground_color', qrForeground.value);
            params.append('background_color', qrBackground.value);
            params.append('margin', qrMargin.value.toString());
        }

        window.location.href = `/qr-code/download?${params}`;
    } finally {
        setTimeout(() => {
            downloading.value = false;
        }, 1000);
    }
};

const applyBrandColors = () => {
    qrForeground.value = props.business.brand_color_primary || '#000000';
    qrBackground.value = '#ffffff';
    posterBgColor.value = props.business.brand_color_secondary || '#f3f4f6';
    updatePreview();
};

const resetToDefault = () => {
    qrForeground.value = '#000000';
    qrBackground.value = '#ffffff';
    qrSize.value = 300;
    qrMargin.value = 0;
    posterBgColor.value = '#f3f4f6';
    updatePreview();
};
</script>

<template>
    <Head title="QR Code Designer" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        QR Code Designer
                    </h1>
                    <p class="text-muted-foreground">
                        Create beautiful, customizable QR codes and posters
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button @click="applyBrandColors" variant="outline">
                        <Palette class="mr-2 h-4 w-4" />
                        Use Brand Colors
                    </Button>
                    <Button @click="resetToDefault" variant="outline">
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Mode Tabs -->
            <div class="flex gap-2">
                <Button
                    @click="activeTab = 'qr'"
                    :variant="activeTab === 'qr' ? 'default' : 'outline'"
                    class="flex-1 md:flex-none"
                >
                    <QrIcon class="mr-2 h-4 w-4" />
                    QR Code Only
                </Button>
                <Button
                    @click="activeTab = 'poster'"
                    :variant="activeTab === 'poster' ? 'default' : 'outline'"
                    class="flex-1 md:flex-none"
                >
                    <Printer class="mr-2 h-4 w-4" />
                    Design Poster
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Customization Panel -->
                <div class="space-y-6">
                    <!-- QR Code Settings -->
                    <Card v-if="activeTab === 'qr'">
                        <CardHeader>
                            <CardTitle>QR Code Customization</CardTitle>
                            <CardDescription>
                                Customize your QR code colors and size
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Size: {{ qrSize }}px</Label>
                                    <Slider
                                        v-model="qrSize"
                                        :min="200"
                                        :max="1000"
                                        :step="50"
                                        class="w-full"
                                    />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="qr_foreground"
                                            >Foreground Color</Label
                                        >
                                        <div class="flex gap-2">
                                            <Input
                                                id="qr_foreground"
                                                v-model="qrForeground"
                                                type="color"
                                                class="h-10 w-16"
                                            />
                                            <Input
                                                v-model="qrForeground"
                                                class="flex-1 font-mono"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="qr_background"
                                            >Background Color</Label
                                        >
                                        <div class="flex gap-2">
                                            <Input
                                                id="qr_background"
                                                v-model="qrBackground"
                                                type="color"
                                                class="h-10 w-16"
                                            />
                                            <Input
                                                v-model="qrBackground"
                                                class="flex-1 font-mono"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>Margin: {{ qrMargin }}px</Label>
                                    <Slider
                                        v-model="qrMargin"
                                        :min="0"
                                        :max="50"
                                        :step="5"
                                        class="w-full"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Poster Settings -->
                    <div v-if="activeTab === 'poster'" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Poster Template</CardTitle>
                                <CardDescription>
                                    Choose a design template
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-3">
                                    <button
                                        v-for="template in templates"
                                        :key="template.value"
                                        @click="posterTemplate = template.value"
                                        :class="[
                                            'flex items-start gap-3 rounded-lg border-2 p-4 text-left transition-all hover:bg-accent',
                                            posterTemplate === template.value
                                                ? 'border-primary bg-accent'
                                                : 'border-border',
                                        ]"
                                    >
                                        <component
                                            :is="template.icon"
                                            class="mt-0.5 h-5 w-5 shrink-0"
                                        />
                                        <div class="flex-1">
                                            <div class="font-medium">
                                                {{ template.label }}
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ template.description }}
                                            </div>
                                        </div>
                                        <Badge
                                            v-if="
                                                posterTemplate ===
                                                template.value
                                            "
                                            >Selected</Badge
                                        >
                                    </button>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Poster Size</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Select
                                    v-model="posterSize"
                                    :options="posterSizes"
                                    option-label="label"
                                    option-value="value"
                                    class="w-full"
                                >
                                    <template #option="{ option }">
                                        <div>
                                            <div class="font-medium">
                                                {{ option.label }}
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ option.description }}
                                            </div>
                                        </div>
                                    </template>
                                </Select>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Poster Customization</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="poster_bg"
                                            >Background Color</Label
                                        >
                                        <div class="flex gap-2">
                                            <Input
                                                id="poster_bg"
                                                v-model="posterBgColor"
                                                type="color"
                                                class="h-10 w-16"
                                            />
                                            <Input
                                                v-model="posterBgColor"
                                                class="flex-1 font-mono"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="poster_text"
                                            >Text Color</Label
                                        >
                                        <div class="flex gap-2">
                                            <Input
                                                id="poster_text"
                                                v-model="posterTextColor"
                                                type="color"
                                                class="h-10 w-16"
                                            />
                                            <Input
                                                v-model="posterTextColor"
                                                class="flex-1 font-mono"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="custom_text"
                                        >Custom Message</Label
                                    >
                                    <Textarea
                                        id="custom_text"
                                        v-model="customText"
                                        :rows="3"
                                        placeholder="Enter a message to appear on your poster..."
                                        class="w-full"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label
                                        >QR Code Size in Poster:
                                        {{ qrSizeInPoster }}px</Label
                                    >
                                    <Slider
                                        v-model="qrSizeInPoster"
                                        :min="400"
                                        :max="1200"
                                        :step="100"
                                        class="w-full"
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>QR Code Customization</CardTitle>
                                <CardDescription>
                                    Customize QR code colors in the poster
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="poster_qr_foreground"
                                            >QR Foreground Color</Label
                                        >
                                        <div class="flex gap-2">
                                            <Input
                                                id="poster_qr_foreground"
                                                v-model="qrForeground"
                                                type="color"
                                                class="h-10 w-16"
                                            />
                                            <Input
                                                v-model="qrForeground"
                                                class="flex-1 font-mono"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="poster_qr_background"
                                            >QR Background Color</Label
                                        >
                                        <div class="flex gap-2">
                                            <Input
                                                id="poster_qr_background"
                                                v-model="qrBackground"
                                                type="color"
                                                class="h-10 w-16"
                                            />
                                            <Input
                                                v-model="qrBackground"
                                                class="flex-1 font-mono"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Download Options -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Download Options</CardTitle>
                            <CardDescription>
                                Choose your preferred format
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-3">
                                <Button
                                    v-for="format in downloadFormats"
                                    :key="format.value"
                                    @click="downloadFile(format.value)"
                                    :disabled="downloading"
                                    variant="outline"
                                    class="h-auto justify-start p-4"
                                >
                                    <component
                                        :is="format.icon"
                                        class="mr-3 h-5 w-5"
                                    />
                                    <div class="flex-1 text-left">
                                        <div class="font-medium">
                                            {{ format.label }}
                                        </div>
                                        <div
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ format.description }}
                                        </div>
                                    </div>
                                    <Download class="ml-2 h-4 w-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Review URL -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Review URL</CardTitle>
                            <CardDescription>
                                This QR code will direct to this URL
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="rounded-lg bg-muted p-3 font-mono text-sm break-all"
                            >
                                {{ reviewUrl }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Preview Panel -->
                <div class="space-y-6">
                    <Card class="sticky top-4">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5" />
                                Live Preview
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="activeTab === 'qr'"
                                class="flex items-center justify-center rounded-lg border-2 border-dashed border-border bg-muted/30 p-8"
                            >
                                <div
                                    class="rounded-lg bg-white p-6 shadow-lg"
                                    :style="{ backgroundColor: qrBackground }"
                                >
                                    <img
                                        :src="previewUrl"
                                        :alt="`QR Code for ${business.name}`"
                                        class="h-auto max-w-full"
                                        :style="{
                                            width: `${Math.min(qrSize, 400)}px`,
                                        }"
                                    />
                                </div>
                            </div>
                            <div
                                v-else
                                class="overflow-hidden rounded-lg border-2 border-dashed border-border"
                                style="height: 700px"
                            >
                                <iframe
                                    :src="previewUrl"
                                    class="h-full w-full"
                                    frameborder="0"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
