<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, Lock, Mail } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout
        title="Reset your password"
        description="Choose a strong password for your account"
    >
        <Head title="Reset password" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium"
                        >Email address</Label
                    >
                    <div class="relative">
                        <Mail
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            v-model="inputEmail"
                            readonly
                            class="bg-muted pl-10"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password" class="text-sm font-medium"
                        >New password</Label
                    >
                    <div class="relative">
                        <Lock
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            autofocus
                            placeholder="••••••••"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-2">
                    <Label
                        for="password_confirmation"
                        class="text-sm font-medium"
                    >
                        Confirm new password
                    </Label>
                    <div class="relative">
                        <Lock
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <Button
                type="submit"
                class="w-full gap-2"
                size="lg"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" />
                <template v-else>
                    Reset password
                    <ArrowRight class="h-4 w-4" />
                </template>
            </Button>
        </Form>
    </AuthLayout>
</template>
