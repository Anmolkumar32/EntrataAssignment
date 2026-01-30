document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('codeForm');
    const explainBtn = document.getElementById('explainBtn');
    const btnText = document.getElementById('btnText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const errorMessage = document.getElementById('errorMessage');
    const explanationContainer = document.getElementById('explanationContainer');
    const keyPartsContainer = document.getElementById('keyPartsContainer');
    const keyPartsList = document.getElementById('keyPartsList');
    const historyContainer = document.getElementById('historyContainer');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Reset UI
        errorMessage.classList.add('hidden');
        errorMessage.textContent = '';
        keyPartsContainer.classList.add('hidden');
        keyPartsList.innerHTML = '';
        setLoading(true);

        const formData = new FormData(form);
        const code = formData.get('code');
        const language = formData.get('language');

        if (!code.trim()) {
            showError('Please enter some code.');
            setLoading(false);
            return;
        }

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code, language }),
            });

            const data = await response.json();

            if (data.success) {
                // Show Explanation
                explanationContainer.innerHTML = `<p>${data.explanation}</p>`;
                
                // Show Key Parts if available
                if (data.key_parts && Array.isArray(data.key_parts) && data.key_parts.length > 0) {
                    keyPartsList.innerHTML = data.key_parts.map(part => `<li>${escapeHtml(part)}</li>`).join('');
                    keyPartsContainer.classList.remove('hidden');
                }

                // Update History
                updateHistory(data.history);
            } else {
                showError(data.error || 'An error occurred.');
            }

        } catch (error) {
            showError('Network error or server issue.');
            console.error(error);
        } finally {
            setLoading(false);
        }
    });

    function setLoading(isLoading) {
        if (isLoading) {
            explainBtn.disabled = true;
            explainBtn.classList.add('opacity-75', 'cursor-not-allowed');
            loadingSpinner.classList.remove('hidden');
            btnText.textContent = 'Analyzing...';
        } else {
            explainBtn.disabled = false;
            explainBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            loadingSpinner.classList.add('hidden');
            btnText.textContent = 'Explain Code';
        }
    }

    function showError(msg) {
        errorMessage.textContent = msg;
        errorMessage.classList.remove('hidden');
    }

    function updateHistory(historyItems) {
        if (!historyItems || historyItems.length === 0) {
            historyContainer.innerHTML = '<p class="text-gray-400 italic text-sm">No history yet.</p>';
            return;
        }

        let html = '';
        historyItems.forEach(item => {
            // Escape HTML to prevent XSS
            const escapedCode = escapeHtml(item.code);
            const escapedLang = escapeHtml(item.language);
            const escapedExpl = escapeHtml(item.explanation);
            const escapedTime = escapeHtml(item.timestamp);
            
            let keyPartsHtml = '';
            if (item.key_parts && Array.isArray(item.key_parts) && item.key_parts.length > 0) {
                keyPartsHtml = `
                    <div class="mt-2 p-2 bg-gray-50 rounded border border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 block mb-1">Key Highlights:</span>
                        <ul class="list-disc list-inside text-xs text-gray-600">
                            ${item.key_parts.map(p => `<li>${escapeHtml(p)}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            html += `
                <div class="border-b border-gray-200 pb-4 last:border-0">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold uppercase text-blue-600 bg-blue-100 px-2 py-1 rounded">${escapedLang}</span>
                        <span class="text-xs text-gray-500">${escapedTime}</span>
                    </div>
                    <pre><code class="language-${escapedLang} text-xs">${escapedCode}</code></pre>
                    <p class="mt-2 text-sm text-gray-700">${escapedExpl}</p>
                    ${keyPartsHtml}
                </div>
            `;
        });

        historyContainer.innerHTML = html;
        
        // Re-run Prism highlighting
        Prism.highlightAllUnder(historyContainer);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
