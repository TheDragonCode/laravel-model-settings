import { readdir, readFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const siteDirectory = path.resolve(
    path.dirname(fileURLToPath(import.meta.url)),
    "..",
);
const config = await readFile(
    path.join(siteDirectory, "docusaurus.config.ts"),
    "utf8",
);
const defaultLocale = config.match(/defaultLocale:\s*["']([^"']+)["']/)?.[1];
const localeBlock = config.match(/locales:\s*\[([^\]]+)]/)?.[1];
const locales = [...(localeBlock?.matchAll(/["']([^"']+)["']/g) ?? [])].map(
    (match) => match[1],
);

if (!defaultLocale || locales.length === 0) {
    throw new Error("Unable to read the Docusaurus locale configuration.");
}

const listMarkdownFiles = async (directory, prefix = "") => {
    const entries = await readdir(directory, { withFileTypes: true });
    const files = [];

    for (const entry of entries) {
        const relativePath = path.posix.join(prefix, entry.name);

        if (entry.isDirectory()) {
            files.push(
                ...(await listMarkdownFiles(
                    path.join(directory, entry.name),
                    relativePath,
                )),
            );
        } else if (entry.isFile() && /\.mdx?$/.test(entry.name)) {
            files.push(relativePath);
        }
    }

    return files.sort();
};

const sourceDirectory = path.join(siteDirectory, "docs");
const sourceFiles = await listMarkdownFiles(sourceDirectory);
const errors = [];

for (const locale of locales.filter((locale) => locale !== defaultLocale)) {
    const localizedDirectory = path.join(
        siteDirectory,
        "i18n",
        locale,
        "docusaurus-plugin-content-docs",
        "current",
    );

    let localizedFiles = [];

    try {
        localizedFiles = await listMarkdownFiles(localizedDirectory);
    } catch (error) {
        if (error.code === "ENOENT") {
            errors.push(`${locale}: localized docs directory is missing`);
            continue;
        }

        throw error;
    }

    const missingFiles = sourceFiles.filter(
        (file) => !localizedFiles.includes(file),
    );
    const extraFiles = localizedFiles.filter(
        (file) => !sourceFiles.includes(file),
    );

    if (missingFiles.length > 0) {
        errors.push(`${locale}: missing ${missingFiles.join(", ")}`);
    }

    if (extraFiles.length > 0) {
        errors.push(`${locale}: unexpected ${extraFiles.join(", ")}`);
    }

    for (const file of sourceFiles.filter((file) =>
        localizedFiles.includes(file),
    )) {
        const [source, localized] = await Promise.all([
            readFile(path.join(sourceDirectory, file), "utf8"),
            readFile(path.join(localizedDirectory, file), "utf8"),
        ]);

        if (source === localized) {
            errors.push(
                `${locale}: ${file} is identical to the English source`,
            );
        }
    }
}

if (errors.length > 0) {
    throw new Error(
        `Incomplete documentation localization:\n${errors.map((error) => `- ${error}`).join("\n")}`,
    );
}

console.log(
    `Documentation localization is complete for ${locales.length - 1} locales and ${sourceFiles.length} pages.`,
);
