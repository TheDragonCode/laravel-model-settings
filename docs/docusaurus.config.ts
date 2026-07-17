import type { Config } from "@docusaurus/types";
import type * as Preset from "@docusaurus/preset-classic";
import { themes as prismThemes } from "prism-react-renderer";

const repositoryUrl = "https://github.com/TheDragonCode/laravel-model-settings";
const siteUrl = "https://model-settings.dragon-code.pro";
const siteDescription =
    "Shared defaults and per-model setting overrides for Laravel Eloquent models.";

const structuredData = {
    "@context": "https://schema.org",
    "@type": "SoftwareSourceCode",
    name: "Laravel Model Settings",
    description: siteDescription,
    url: siteUrl,
    codeRepository: repositoryUrl,
    license: `${repositoryUrl}/blob/main/LICENSE`,
    programmingLanguage: "PHP",
    runtimePlatform: "Laravel",
    author: {
        "@type": "Organization",
        name: "The Dragon Code",
        url: "https://github.com/TheDragonCode",
    },
};

const config: Config = {
    title: "Laravel Model Settings",
    tagline: "Model Settings for Laravel applications",
    url: siteUrl,
    baseUrl: "/",
    favicon: "img/logo-64.png",
    organizationName: "TheDragonCode",
    projectName: "laravel-model-settings",
    trailingSlash: true,
    onBrokenLinks: "throw",
    future: {
        v4: true,
    },
    headTags: [
        {
            tagName: "script",
            attributes: {
                type: "application/ld+json",
            },
            innerHTML: JSON.stringify(structuredData),
        },
    ],
    i18n: {
        defaultLocale: "en",
        locales: ["en", "ru", "uk", "be", "fr", "pt-BR", "ko", "zh-CN", "de"],
        localeConfigs: {
            en: {
                label: "English",
                htmlLang: "en-US",
            },
            ru: {
                label: "Русский",
                htmlLang: "ru-RU",
            },
            uk: {
                label: "Українська",
                htmlLang: "uk-UA",
            },
            be: {
                label: "Беларуская",
                htmlLang: "be-BY",
            },
            fr: {
                label: "Français",
                htmlLang: "fr-FR",
            },
            "pt-BR": {
                label: "Português (Brasil)",
                htmlLang: "pt-BR",
            },
            ko: {
                label: "한국어",
                htmlLang: "ko-KR",
            },
            "zh-CN": {
                label: "简体中文",
                htmlLang: "zh-CN",
            },
            de: {
                label: "Deutsch",
                htmlLang: "de-DE",
            },
        },
    },
    presets: [
        [
            "classic",
            {
                docs: {
                    routeBasePath: "/",
                    sidebarPath: "./sidebars.ts",
                    editUrl: `${repositoryUrl}/tree/main/docs/`,
                    editLocalizedFiles: true,
                    lastVersion: "current",
                    versions: {
                        current: {
                            label: "Current",
                            path: "",
                            banner: "none",
                        },
                    },
                },
                blog: false,
            } satisfies Preset.Options,
        ],
    ],
    themeConfig: {
        image: "img/preview.jpg",
        metadata: [
            {
                name: "keywords",
                content:
                    "Laravel, Eloquent, model settings, Laravel settings, PHP",
            },
            {
                name: "twitter:card",
                content: "summary_large_image",
            },
        ],
        colorMode: {
            defaultMode: "light",
            disableSwitch: false,
            respectPrefersColorScheme: true,
        },
        navbar: {
            title: "Laravel Model Settings",
            logo: {
                alt: "Laravel Model Settings",
                src: "img/logo-64.png",
            },
            items: [
                {
                    type: "docsVersionDropdown",
                    position: "left",
                },
                {
                    type: "localeDropdown",
                    position: "right",
                },
                {
                    href: repositoryUrl,
                    label: "GitHub",
                    position: "right",
                },
            ],
        },
        footer: {
            style: "dark",
            copyright: `Copyright © ${new Date().getFullYear()} The Dragon Code`,
        },
        prism: {
            theme: prismThemes.github,
            darkTheme: prismThemes.dracula,
        },
    } satisfies Preset.ThemeConfig,
};

export default config;
