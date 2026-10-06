const punjabiInscriptMap = {
    'q': 'ੌ', 'w': 'ੈ', 'e': 'ਾ', 'r': 'ੀ', 't': 'ੂ', 'y': 'ਬ', 'u': 'ਹ', 'i': 'ਗ', 'o': 'ਦ', 'p': 'ਜ', '[': 'ਡ', ']': '਼', '\\': 'ੌ',
    'a': 'ੋ', 's': 'ੇ', 'd': '੍', 'f': 'ਿ', 'g': 'ੁ', 'h': 'ਪ', 'j': 'ਰ', 'k': 'ਕ', 'l': 'ਤ', ';': 'ਚ', '\'': 'ਟ',
    'z': 'ਞ', 'x': 'ੰ', 'c': 'ਮ', 'v': 'ਨ', 'b': 'ਵ', 'n': 'ਲ', 'm': 'ਸ', ',': ',', '.': '.', '/': 'ਯ',
    'Q': 'ਔ', 'W': 'ਐ', 'E': 'ਆ', 'R': 'ਈ', 'T': 'ਊ', 'Y': 'ਭ', 'U': 'ਙ', 'I': 'ਘ', 'O': 'ਧ', 'P': 'ਝ', '{': 'ਢ', '}': 'ਣ', '|': 'ਔ',
    'A': 'ਓ', 'S': 'ਏ', 'D': 'ਅ', 'F': 'ਇ', 'G': 'ਉ', 'H': 'ਫ', 'J': 'ੜ', 'K': 'ਖ', 'L': 'ਥ', ':': 'ਛ', '"': 'ਠ',
    'Z': 'ਁ', 'X': 'ਂ', 'C': 'ਣ', 'V': 'ਨ', 'B': '਴', 'N': 'ਲ਼', 'M': 'ਸ਼', '<': '਷', '>': '।', '?': 'ਯ',
    '`': 'ੌ', '~': 'ਔ', '1': '੧', '2': '੨', '3': '੩', '4': '੪', '5': '੫', '6': '੬', '7': '੭', '8': '੮', '9': '੯', '0': '੦',
    '-': '-', '=': 'ृ', '_': 'ੳ', '+': 'ਃ'
};

function enablePunjabiInscript(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;

    el.addEventListener('keypress', function(e) {
        if (e.ctrlKey || e.altKey || e.metaKey) return;
        
        const charCode = e.which || e.keyCode;
        const charStr = String.fromCharCode(charCode);
        
        if (punjabiInscriptMap[charStr]) {
            e.preventDefault();
            
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const text = this.value;
            
            const mappedChar = punjabiInscriptMap[charStr];
            this.value = text.substring(0, start) + mappedChar + text.substring(end);
            
            this.selectionStart = this.selectionEnd = start + mappedChar.length;
            
            // Dispatch input event for game logic to register the change natively
            const event = new Event('input', { bubbles: true });
            this.dispatchEvent(event);
        }
    });
}
