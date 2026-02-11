export function replaceUnicodeRecursive(data) {
    const spaceRegex = /[ \t\v\f\u00A0\u2000-\u200B\u202F\u205F\u3000]+/gu;

    if (Array.isArray(data)) {
        return data.map((v) => replaceUnicodeRecursive(v));
    }

    if (data !== null && typeof data === 'object') {
        const out = {};
        for (const k in data) {
            if (Object.prototype.hasOwnProperty.call(data, k)) {
                out[k] = replaceUnicodeRecursive(data[k]);
            }
        }
        return out;
    }

    if (typeof data === 'string') {
        // collapse runs of various whitespace into a single ASCII space
        const collapsed = data.replace(spaceRegex, ' ');
        return replaceUnicode(collapsed);
    }

    return data;
}
export function replaceUnicode(text) {
    if (!text || text === '""' || text === 'null') {
        return null;
    }
    if (typeof text !== "string") {
        return text;
    }
    const salabpi = ["ង", "ញ", "ប", "ម", "យ", "រ", "វ"];
    const treysab = ["ស", "ហ", "អ"];
    const chars = salabpi.concat(treysab);
    const vowels = ["ិ", "ី", "ឹ", "ឺ", "ើ"];
    text = text
        .replaceAll("ា" + "ំ", "ាំ")
        .replaceAll("េ" + "ី", "ើ")
        .replaceAll("េ" + "ា", "ោ")
        .replaceAll("េ" + "ះ", "េះ")
        .replaceAll("ោ" + "ះ", "ោះ")
        .replaceAll("េ" + "ុ" + "ី", "ុ" + "ើ");
    for (const char of chars) {
        for (const vowel of vowels) {
            let replacementSign = "";
            if (salabpi.includes(char)) {
                replacementSign = "៉";
            } else if (treysab.includes(char)) {
                replacementSign = "៊";
            } else {
                continue;
            }
            const word = char + "ុ" + vowel;
            const replacement = char + replacementSign + vowel;
            text = text.replaceAll(word, replacement);
        }
    }
    return text;
}