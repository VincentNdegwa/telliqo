<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/two-factor/login';
import { Form, Head } from '@inertiajs/vue3';
import { Shield, Key, ArrowRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
    icon: any;
}

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description:
                'Enter one of your emergency recovery codes to access your account.',
            toggleText: 'Use authentication code instead',
            icon: Key
        };
    }

    return {
        title: 'Two-Factor Authentication',
        description:
            'Enter the 6-digit code from your authenticator app.',
        toggleText: 'Use recovery code instead',
        icon: Shield
    };
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = [];
};

const code = ref<number[]>([]);
const codeValue = computed<string>(() => code.value.join(''));
</script>

<template>
    <AuthLayout
        :title="authConfigContent.title"
        :description="authConfigContent.description"
    >
        <Head title="Two-Factor Authentication" />

        <div class="space-y-6">
            <!-- Icon Display -->
            <div class="flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                    <component :is="authConfigContent.icon" class="h-10 w-10 text-primary" />
                </div>
            </div>

            <!-- Authentication Code Form -->
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    class="space-y-4"
                    reset-on-error
                    @error="code = []"
                    #default="{ errors, processing, clearErrors }"
                >
                    <input type="hidden" name="code" :value="codeValue" />
                    <div class="space-y-3">
                        <div class="flex w-full items-center justify-center">
                            <PinInput
                                id="otp"
                                placeholder="○"
                                v-model="code"
                                type="number"
                                otp
                            >
                                <PinInputGroup>
                                    <PinInputSlot
                                        v-for="(id, index) in 6"
                                        :key="id"
                                        :index="index"
                                        :disabled="processing"
                                        autofocus
                                    />
                                </PinInputGroup>
                            </PinInput>
                        </div>
                        <InputError :message="errors.code" class="text-center" />
                    </div>
                    
                    <Button 
                        type="submit" 
                        class="w-full gap-2" 
                        size="lg"
                        :disabled="processing"
                    >
                        <template v-if="processing">
                            <Spinner />
                        </template>
                        <template v-else>
                            Verify
                            <ArrowRight class="h-4 w-4" />
                        </template>
                    </Button>
                    
                    <div class="text-center">
                        <button
                            type="button"
                            class="text-sm font-medium text-muted-foreground hover:text-foreground underline-offset-4 hover:underline"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>

            <!-- Recovery Code Form -->
            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-4"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                >
                    <div class="space-y-2">
                        <Input
                            name="recovery_code"
                            type="text"
                            placeholder="XXXXX-XXXXX"
                            :autofocus="showRecoveryInput"
                            required
                            class="text-center font-mono text-lg"
                        />
                        <InputError :message="errors.recovery_code" class="text-center" />
                    </div>
                    
                    <Button 
                        type="submit" 
                        class="w-full gap-2" 
                        size="lg"
                        :disabled="processing"
                    >
                        <template v-if="processing">
                            <Spinner />
                        </template>
                        <template v-else>
                            Verify
                            <ArrowRight class="h-4 w-4" />
                        </template>
                    </Button>

                    <div class="text-center">
                        <button
                            type="button"
                            class="text-sm font-medium text-muted-foreground hover:text-foreground underline-offset-4 hover:underline"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
