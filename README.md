# ⚡ FinPilot AI — AI Financial Co-Pilot

A complete, production-ready **AI-powered financial planning system** built for Indian users.
Get a personalized money health score, behavioral insights, financial personality analysis,
and 4 AI-generated investment roadmaps.

---

## 🗂️ Project Structure

```
finpilot/
├── index.html          ← Landing page
├── signup.php          ← User registration
├── login.php           ← User login
├── logout.php          ← Session logout
├── profile.php         ← Financial profile form (income, goals, risk)
├── questions.php       ← 5 behavioral assessment questions
├── processing.html     ← Animated loading screen (redirects to processing.php)
├── processing.php      ← AI API call + result storage
├── dashboard.php       ← Score, insights, personality display
├── roadmap.php         ← 4 AI-generated financial plan cards
├── chat.php            ← AI financial advisor chatbot
├── select_plan.php     ← AJAX handler for plan selection
├── schema.sql          ← Database schema
├── css/
│   └── style.css       ← Full design system (dark theme, Syne + DM Sans)
├── js/
│   └── utils.js        ← Shared JS utilities
└── includes/
    └── config.php      ← DB config, AI config, helper functions
```

---

## 🚀 Quick Setup

### 1. Requirements
- PHP 8.0+ with cURL enabled
- MySQL 5.7+ / MariaDB 10.4+
- Apache / Nginx with mod_rewrite (XAMPP / WAMP / Laragon / live server)
- OpenAI API key (gpt-4o-mini) — get one at [platform.openai.com](https://platform.openai.com)

---

### 2. Database Setup

Open **phpMyAdmin** or run in MySQL terminal:

```sql
SOURCE /path/to/finpilot/schema.sql;
```

Or paste the contents of `schema.sql` into phpMyAdmin's SQL tab.

This creates the `finpilot` database with 5 tables:
- `users` — auth
- `financial_data` — profile inputs
- `behavioral_responses` — 5 question answers
- `ai_results` — cached AI analysis
- `chat_history` — chat messages

---

### 3. Configure API & Database

Open `includes/config.php` and update:

```php
// ── DB CONFIG ─────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_NAME', 'finpilot');

// ── AI API KEY ────────────────────────────────
define('AI_API_KEY', 'sk-...');   // ← PASTE YOUR OPENAI KEY HERE
define('AI_MODEL',   'gpt-4o-mini');
```

---

### 4. Place Project Files

For **XAMPP** (Windows):
```
C:/xampp/htdocs/finpilot/
```

For **WAMP**:
```
C:/wamp64/www/finpilot/
```

For **Linux/Mac LAMP**:
```
/var/www/html/finpilot/
```

Then open: `http://localhost/finpilot/`

---

## 🎯 User Flow

```
index.html          → Landing page
  ↓ "Start Analysis"
signup.php          → Create account
  ↓
profile.php         → Enter: Age, Income, Expenses, Savings, Debt, Risk, Goals
  ↓
questions.php       → 5 behavioral questions (Yes/No/Not Sure)
  ↓
processing.html     → Animated loading (3.6s) → redirects to processing.php
  ↓
processing.php      → Calls OpenAI API, saves result to DB
  ↓
dashboard.php       → Shows: Score ring, Insights, Personality, Stats
  ↓ "View Plans"
roadmap.php         → 4 plan cards (Safe / Balanced / Growth / Goal-Based)
  ↓ "Choose Plan"
select_plan.php     → AJAX saves selected plan
  ↓
chat.php            → AI chat with full financial context
```

---

## 🤖 AI Integration

### Prompt Structure (`includes/config.php → buildFinancialPrompt()`)

The AI receives:
- Complete financial profile (income, expenses, savings, debt, risk)
- 5 behavioral answers
- Goals list

And returns JSON:
```json
{
  "score": 68,
  "scoreLabel": "Good",
  "insights": ["...3 insights..."],
  "personality": "Balanced Planner",
  "personalityIcon": "⚖️",
  "personalityDesc": "...",
  "stats": { "savingsRate": 20, "debtRatio": 30, "expenseRatio": 65, "investmentScore": 45 },
  "plans": [
    { "name": "Safe Plan", "type": "safe", "badge": "🛡️ Low Risk",
      "description": "...", "steps": ["step1","step2","step3"] },
    { ... },
    { ... },
    { ... }
  ]
}
```

### Chat Context

The chatbot receives the user's full financial profile as system context so every answer is personalized to their actual numbers and goals.

---

## 🎨 Tech Stack

| Layer     | Technology |
|-----------|-----------|
| Frontend  | HTML5, CSS3 (custom design system), Vanilla JS |
| Styling   | CSS Variables, Tailwind-inspired utilities |
| Fonts     | Syne (headings) + DM Sans (body) via Google Fonts |
| Animation | AOS (Animate on Scroll) v2.3.4 |
| Backend   | PHP 8.0+ |
| Database  | MySQL / MariaDB |
| AI        | OpenAI gpt-4o-mini via REST API |
| HTTP      | PHP cURL |

---

## 🔥 Key Features

- ✅ **Money Health Score** — 0–100 animated ring with color coding
- ✅ **4 AI Roadmaps** — Safe, Balanced, Growth, Goal-Based with 3 steps each
- ✅ **Financial Personality** — AI-generated archetype with icon
- ✅ **Stat Bars** — Savings rate, debt ratio, expense ratio, investment score
- ✅ **Plan Selection** — AJAX select + persistent storage
- ✅ **Plan Comparison Table** — Side-by-side risk/return comparison
- ✅ **AI Chatbot** — Context-aware with full profile + history
- ✅ **Quick Suggestions** — Pre-filled chat prompts
- ✅ **AOS Animations** — Scroll-triggered reveals throughout
- ✅ **Responsive** — Mobile-optimized layouts
- ✅ **Progress Stepper** — 4-step visual progress indicator
- ✅ **Risk Selector** — Interactive Low/Medium/High toggle
- ✅ **Goal Multi-Select** — Checkbox grid for 9 goal types

---

## ⚙️ Customization

### Change AI Model
In `includes/config.php`:
```php
define('AI_MODEL', 'gpt-4o');          // Smarter but costs more
define('AI_MODEL', 'gpt-3.5-turbo');   // Cheaper, faster
```

### Use a Different API (Groq, Anthropic, etc.)
Change `AI_API_URL` and adjust the response parsing in `callAI()` to match the provider's response format.

### Add More Goals
In `profile.php`, add to the `$goalOptions` array:
```php
['id'=>'Car', 'icon'=>'🚗', 'label'=>'Car'],
```

---

## ⚠️ Disclaimer

FinPilot AI is for **educational and informational purposes only**.
It is **not SEBI registered** and does not constitute financial advice.
Always consult a certified financial advisor before making investment decisions.

---

## 📦 Dependencies (CDN, no npm needed)

- AOS: `https://unpkg.com/aos@2.3.4/dist/aos.js`
- Google Fonts: Syne + DM Sans (loaded in CSS)

No build step, no npm, no bundler required. Drop the folder into any PHP server and go.
