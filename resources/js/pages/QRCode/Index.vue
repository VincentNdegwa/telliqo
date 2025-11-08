<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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
import { Business } from '@/types/business';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import qrCode from '@/routes/qr-code';
import feedback from '@/routes/feedback';
import { Download, RefreshCw, QrCode, Copy, Check } from 'lucide-vue-next';
import { ref } from 'vue';

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
        title: 'QR Code',
        href: qrCode.index().url,
    },
];

const copied = ref(false);
const regenerating = ref(false);

const copyToClipboard = async () => {
    await navigator.clipboard.writeText(props.reviewUrl);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const downloadQRCode = () => {
    window.location.href = qrCode.download.url();
};

const regenerateQRCode = () => {
    if (confirm('Are you sure you want to regenerate your QR code? The old QR code will stop working.')) {
        regenerating.value = true;
        router.post(qrCode.regenerate.url(), {}, {
            onFinish: () => {
                regenerating.value = false;
            },
        });
    }
};
</script>

<template>
    <Head title="QR Code" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">QR Code</h1>
                    <p class="text-muted-foreground">
                        Download and share your feedback collection QR code
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button @click="downloadQRCode" variant="outline">
                        <Download class="mr-2 h-4 w-4" />
                        Download
                    </Button>
                    <Button @click="regenerateQRCode" :disabled="regenerating" variant="outline">
                        <RefreshCw :class="{ 'animate-spin': regenerating }" class="mr-2 h-4 w-4" />
                        Regenerate
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- QR Code Display -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <QrCode class="h-5 w-5" />
                            Your QR Code
                        </CardTitle>
                        <CardDescription>
                            Customers can scan this code to leave feedback
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex justify-center">
                        <div class="rounded-lg border-2 border-border bg-white p-8">
                            <img
                                :src="qrCodeUrl"
                                :alt="`QR Code for ${business.name}`"
                                class="h-64 w-64"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Instructions & URL -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Review URL</CardTitle>
                            <CardDescription>
                                Share this link with your customers
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex gap-2">
                                <Input
                                    :modelValue="reviewUrl"
                                    readonly
                                    class=""
                                />
                                <Button @click="copyToClipboard" size="icon" variant="outline">
                                    <Check v-if="copied" class="h-4 w-4 text-green-600" />
                                    <Copy v-else class="h-4 w-4" />
                                </Button>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                This URL points to your public feedback form
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>How to Use</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ol class="space-y-3 text-sm">
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        1
                                    </span>
                                    <span>Download the QR code using the button above</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        2
                                    </span>
                                    <span>Print it and display it at your business location</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        3
                                    </span>
                                    <span>Customers scan the code to leave feedback instantly</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        4
                                    </span>
                                    <span>View and manage all feedback in your dashboard</span>
                                </li>
                            </ol>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Tips</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2 text-sm text-muted-foreground">
                                <li>• Place the QR code near your checkout or exit</li>
                                <li>• Include a call-to-action like "Scan to share your experience"</li>
                                <li>• Test the QR code with your phone before printing</li>
                                <li>• Use high-quality printing for best scanning results</li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
