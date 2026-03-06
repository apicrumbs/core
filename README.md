# ApiCrumbs Core
**The Token-Optimised Context Engine for PHP AI Agents.**

**ApiCrumbs** is a zero-dependency PHP engine designed to solve the "JSON Tax." It flattens bloated API responses from 250+ providers into high-signal, **Markdown-KV** context blocks (Crumbs), reducing your LLM token costs by up to 92%.

**The Universal PHP Data Supply Chain for LLMs.**

**ApiCrumbs** is a Sponsorware framework designed to fetch, normalize, and feed global data—from Companies House to real-time Meteo—directly into LLM contexts. Stop writing custom API wrappers; let the Foundry assemble your Knowledge Base.

---

## What is it?

The Foundry Core is the backbone of the ApiCrumbs ecosystem. It provides:

* **Unified Interface:** One standard way to query 250+ different data providers.
* **LLM-Ready Output:** Data is automatically formatted for RAG (Retrieval-Augmented Generation).
* **The Registry:** A modular system to install "Crumbs" (Adapters) for specific APIs.
  
---

## Why ApiCrumbs?

In 2026, passing raw JSON into **GPT-4.5** or **Claude 4** is an expensive mistake.
*   ** Token ROI:** A 2,400-token Companies House JSON becomes a **180-token Crumb**.
*   ** 2026 Ready:** Built-in support for the April 2026 HMRC MTD v2 and Companies House reforms.
*   ** Local-First:** No data leaves your server. It's a library, not a SaaS.
*   ** The Foundry CLI:** Install, update, and audit 250+ data providers in one command.

---

## Installation

Install the engine via Composer:

```bash
# Install the engine via Composer
composer require apicrumbs/core
```

---

## How it Works

The Foundry uses a Provider Pattern. You install a "Crumb" for a specific data source (e.g., Postcode or Weather), and the Foundry manages the authentication, rate limiting, and output normalization.

## The Foundry CLI

ApiCrumbs comes with a native CLI to manage your data registry. No dependencies required. 

```bash
# List available providers (Geo, Finance, Compliance, etc.)
php vendor/bin/foundry list

# Install a free provider (e.g., Postcodes.io)
php vendor/bin/foundry install geo/postcodes
```

## Quick Start

Bake high-signal context for your AI agent in 3 lines of code.

```bash
use ApiCrumbs\Core\ApiCrumbs;
use ApiCrumbs\Providers\Geo\PostcodeProvider;

$crumbs = new ApiCrumbs();

// 1. Register the provider
$crumbs->registerProvider(new PostcodeProvider());

// 2. Build the "Grounded" context for your LLM
echo $crumbs->build('SW1A 1AA'); 

```

## The Output (Optimised for AI Reasoning)

```
### GEO_LOCATION: SW1A 1AA
- **REGION**: London
- **WARD**: St James's
- **COMPLIANCE**: 2026-Ready
---
```

## Founding Sponsors (4/10 Slots Remaining)

Be one of the first 10 to lock in Business Pro access to the private UK Compliance & Merchant Packs.

---

## Pro & Global Registry

While the Core and Free Registry (250+ packages) are MIT-licensed, high-compliance adapters are available via GitHub Sponsorship:
Business Pro: UK Gov Stack (HMRC, Companies House, Crime API, NHS).
Global Ops: USA (SEC/Census), EU (VIES), and AU Data Packs + Custom Adapter Requests.
Become a Sponsor to Unlock Pro Repositories

---

## Documentation
Auto-generated documentation for every provider in the registry is available at docs.apicrumbs.io.

---

Contributing
Found a public API? Add it to the Free Registry! Submit a PR to the Registry Repository.

