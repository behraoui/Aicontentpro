// WordPress Plugin Demo JavaScript

function demoGenerate() {
    const topic = document.getElementById('topic').value.trim();
    const focusKeyword = document.getElementById('focus_keyword').value.trim();
    
    if (!topic || !focusKeyword) {
        alert('Please fill in both topic and focus keyword fields.');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.classList.add('loading');
    button.disabled = true;
    
    // Show progress
    showProgress(0, 'Connecting to OpenRouter API...');
    
    // Simulate generation process
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 20;
        
        if (progress === 20) {
            showProgress(progress, 'Generating article content with AI...');
        } else if (progress === 40) {
            showProgress(progress, 'Optimizing content for SEO...');
        } else if (progress === 60) {
            showProgress(progress, 'Fetching featured image from Unsplash...');
        } else if (progress === 80) {
            showProgress(progress, 'Adding internal and external links...');
        } else if (progress === 100) {
            showProgress(progress, 'Article generated successfully!');
            clearInterval(progressInterval);
            
            // Show result
            showResult({
                success: true,
                title: `"${topic}" - SEO Optimized Article`,
                message: `Article successfully generated with focus keyword "${focusKeyword}" and optimized for RankMath.`,
                post_id: 123
            });
            
            // Reset form
            document.getElementById('single-article-form').reset();
            
            // Reset button
            setTimeout(() => {
                button.classList.remove('loading');
                button.disabled = false;
                button.innerHTML = originalText;
                hideProgress();
            }, 2000);
        }
    }, 1000);
}

function demoBatchGenerate() {
    const keywordList = document.getElementById('keyword_list').value.trim();
    
    if (!keywordList) {
        alert('Please enter at least one keyword.');
        return;
    }
    
    const keywords = keywordList.split('\n').map(k => k.trim()).filter(k => k.length > 0);
    
    if (keywords.length === 0) {
        alert('Please enter valid keywords.');
        return;
    }
    
    if (keywords.length > 10) {
        alert('Maximum 10 keywords allowed for batch generation.');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.classList.add('loading');
    button.disabled = true;
    
    // Clear previous results
    clearResults();
    
    // Process keywords
    processBatchKeywords(keywords, 0, () => {
        button.classList.remove('loading');
        button.disabled = false;
        button.innerHTML = originalText;
        document.getElementById('batch-articles-form').reset();
        setTimeout(hideProgress, 3000);
    });
}

function processBatchKeywords(keywords, currentIndex, callback) {
    if (currentIndex >= keywords.length) {
        showProgress(100, 'Batch generation completed!');
        callback();
        return;
    }
    
    const keyword = keywords[currentIndex];
    const progress = Math.round(((currentIndex + 1) / keywords.length) * 100);
    
    showProgress(progress, `Generating article ${currentIndex + 1} of ${keywords.length}: ${keyword}`);
    
    // Simulate individual article generation
    setTimeout(() => {
        showResult({
            success: Math.random() > 0.1, // 90% success rate
            title: `Article for "${keyword}"`,
            message: Math.random() > 0.1 ? 
                `SEO-optimized article generated successfully for keyword "${keyword}"` :
                `Failed to generate article for "${keyword}" - API rate limit exceeded`,
            keyword: keyword,
            post_id: 100 + currentIndex
        });
        
        // Process next keyword
        setTimeout(() => {
            processBatchKeywords(keywords, currentIndex + 1, callback);
        }, 500);
    }, 1500);
}

function testApiConnection() {
    const button = event.target;
    const originalText = button.textContent;
    
    button.classList.add('loading');
    button.disabled = true;
    
    // Simulate API test for multiple providers
    setTimeout(() => {
        const apiStatus = document.getElementById('api-status');
        const configuredApis = [];
        
        // Simulate some APIs configured for demo
        if (Math.random() > 0.7) configuredApis.push('OpenAI');
        if (Math.random() > 0.8) configuredApis.push('Claude');
        if (Math.random() > 0.9) configuredApis.push('DeepSeek');
        
        if (configuredApis.length > 0) {
            apiStatus.innerHTML = '<div class="api-status-warning">⚠ Configured: ' + configuredApis.join(', ') + '. Please add remaining API keys.</div>';
        } else {
            apiStatus.innerHTML = '<div class="api-status-error">✗ No API keys configured. Please add OpenAI, Claude, DeepSeek, or OpenRouter API keys in settings.</div>';
        }
        
        button.classList.remove('loading');
        button.disabled = false;
        button.textContent = originalText;
    }, 2000);
}

function showSettings() {
    document.getElementById('settings-modal').style.display = 'block';
}

function showLogs() {
    alert('Logs page would open here in the actual WordPress admin interface.');
}

function closeModal() {
    document.getElementById('settings-modal').style.display = 'none';
}

function showProgress(percentage, message) {
    const progressContainer = document.getElementById('generation-progress');
    const progressFill = progressContainer.querySelector('.progress-fill');
    const progressStatus = document.getElementById('progress-status');
    
    progressFill.style.width = percentage + '%';
    progressStatus.textContent = message;
    progressContainer.style.display = 'block';
}

function hideProgress() {
    document.getElementById('generation-progress').style.display = 'none';
}

function showResult(result) {
    const resultsContainer = document.getElementById('generation-results');
    const resultsContent = document.getElementById('results-content');
    
    const resultClass = result.success ? 'success' : 'error';
    const resultIcon = result.success ? '✓' : '✗';
    
    const resultDiv = document.createElement('div');
    resultDiv.className = `generation-result ${resultClass}`;
    
    let resultHtml = `<div class="generation-result-title">${resultIcon} ${result.title}</div>`;
    resultHtml += `<div class="generation-result-message">${result.message}</div>`;
    
    if (result.success && result.post_id) {
        resultHtml += '<div class="generation-result-actions" style="margin-top: 10px;">';
        resultHtml += `<button class="button button-small" onclick="alert('Would open post editor for post ID: ${result.post_id}')">Edit Post</button>`;
        resultHtml += '</div>';
    }
    
    resultDiv.innerHTML = resultHtml;
    resultsContent.appendChild(resultDiv);
    resultsContainer.style.display = 'block';
    
    // Scroll to results
    resultsContainer.scrollIntoView({ behavior: 'smooth' });
}

function clearResults() {
    document.getElementById('results-content').innerHTML = '';
    document.getElementById('generation-results').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('settings-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Initialize demo
document.addEventListener('DOMContentLoaded', function() {
    console.log('AutoContent AI Pro Demo Loaded');
});