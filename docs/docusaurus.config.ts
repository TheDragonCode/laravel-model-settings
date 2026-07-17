import type { Config } from "@docusaurus/types";
import type * as Preset from "@docusaurus/preset-classic";
import { themes as prismThemes } from "prism-react-renderer";

const repositoryUrl = "https://github.com/TheDragonCode/laravel-model-settings";

const config: Config = {
    title: "Laravel Model Settings",
    tagline: "Model Settings for Laravel applications",
    url: "https://model-settings.dragon-code.pro",
    baseUrl: "/",
    favicon: "img/logo-64.png",
    organizationName: "TheDragonCode",
    projectName: "laravel-model-settings",
    trailingSlash: true,
    onBrokenLinks: "throw",
    future: {
        v4: true,
    },
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
