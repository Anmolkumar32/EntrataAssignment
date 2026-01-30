<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Code Explainer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen flex flex-col">

    <header class="bg-blue-600 text-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">AI Code Explainer</h1>
            <p class="text-sm opacity-80">Powered by OpenAI</p>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 flex-grow">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Input Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Input Code</h2>
                <form id="codeForm" class="flex flex-col gap-4">
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                        <select id="language" name="language" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2 border">
                            <option value="python">Python</option>
                            <option value="javascript">JavaScript</option>
                        </select>
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Code Snippet</label>
                        <textarea id="code" name="code" rows="10" class="w-full font-mono text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2 border" placeholder="Paste your code here..."></textarea>
                    </div>

                    <button type="submit" id="explainBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center justify-center">
                        <span id="btnText">Explain Code</span>
                        <svg id="loadingSpinner" class="animate-spin ml-2 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    <div id="errorMessage" class="text-red-500 text-sm hidden"></div>
                </form>
            </div>

            <!-- Output Section -->
            <div class="flex flex-col gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Explanation</h2>
                    <div id="explanationContainer" class="prose max-w-none text-gray-700">
                        <p class="text-gray-400 italic">Submit code to see the explanation here.</p>
                    </div>
                    
                    <div id="keyPartsContainer" class="mt-6 hidden">
                        <h3 class="text-lg font-medium mb-2 text-gray-800">Key Highlights</h3>
                        <ul id="keyPartsList" class="list-disc list-inside space-y-1 text-sm text-gray-600 bg-gray-50 p-4 rounded border border-gray-200">
                            <!-- Items will be injected here -->
                        </ul>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">History</h2>
                    <div id="historyContainer" class="space-y-4 max-h-96 overflow-y-auto">
                        <?php
                        if (isset($_SESSION['history']) && !empty($_SESSION['history'])):
                            foreach ($_SESSION['history'] as $index => $item):
                        ?>
                            <div class="border-b border-gray-200 pb-4 last:border-0">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold uppercase text-blue-600 bg-blue-100 px-2 py-1 rounded"><?php echo htmlspecialchars($item['language']); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($item['timestamp']); ?></span>
                                </div>
                                <pre><code class="language-<?php echo htmlspecialchars($item['language']); ?> text-xs"><?php echo htmlspecialchars($item['code']); ?></code></pre>
                                <p class="mt-2 text-sm text-gray-700"><?php echo htmlspecialchars($item['explanation']); ?></p>
                                <?php if (isset($item['key_parts']) && is_array($item['key_parts']) && !empty($item['key_parts'])): ?>
                                    <div class="mt-2 p-2 bg-gray-50 rounded border border-gray-100">
                                        <span class="text-xs font-semibold text-gray-500 block mb-1">Key Highlights:</span>
                                        <ul class="list-disc list-inside text-xs text-gray-600">
                                            <?php foreach ($item['key_parts'] as $part): ?>
                                                <li><?php echo htmlspecialchars($part); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <p class="text-gray-400 italic text-sm">No history yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="bg-gray-800 text-gray-400 py-4 text-center text-sm">
        &copy; <?php echo date('Y'); ?> AI Code Explainer. All rights reserved.
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
