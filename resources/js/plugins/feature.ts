import { usePage } from '@inertiajs/vue3';
import type { DirectiveBinding, Plugin } from 'vue';

const hasFeature = (feature: string | string[]): boolean => {
    const page = usePage();
    const features = (page.props.auth as any)?.features || [];

    if (Array.isArray(feature)) {
        return feature.some((f) => features.includes(f));
    }

    return features.includes(feature);
};

const hasAllFeatures = (featuresToCheck: string[]): boolean => {
    const page = usePage();
    const features = (page.props.auth as any)?.features || [];

    return featuresToCheck.every((f) => features.includes(f));
};

const featureDirective = {
    mounted(el: HTMLElement, binding: DirectiveBinding) {
        const { value, modifiers } = binding;

        if (!value) {
            console.warn(
                'v-feature directive requires a feature key or array of keys',
            );
            return;
        }

        const checkFn = modifiers.all ? hasAllFeatures : hasFeature;
        const allowed = Array.isArray(value)
            ? checkFn(value)
            : hasFeature(value);

        if (!allowed) {
            el.style.display = 'none';
        }
    },

    updated(el: HTMLElement, binding: DirectiveBinding) {
        const { value, modifiers } = binding;

        if (!value) {
            return;
        }

        const checkFn = modifiers.all ? hasAllFeatures : hasFeature;
        const allowed = Array.isArray(value)
            ? checkFn(value)
            : hasFeature(value);

        if (allowed) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    },
};

const FeaturePlugin: Plugin = {
    install(app) {
        app.directive('feature', featureDirective);
        app.config.globalProperties.$hasFeature = hasFeature;
        app.config.globalProperties.$hasAllFeatures = hasAllFeatures;
    },
};

export { hasFeature, hasAllFeatures };
export default FeaturePlugin;
