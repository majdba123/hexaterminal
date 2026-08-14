/**
 * Hand-mirrored TypeScript contract for /api/v1/public/* responses.
 * Source of truth: Laravel's App\Http\Resources\V1\Public\* classes and
 * docs/architecture/nextjs-laravel-boundary.md. If the backend shape
 * changes, update both.
 */

export interface SeoMeta {
  title: string | null;
  description: string | null;
  canonical_url: string | null;
  og_image: string | null;
  noindex: boolean;
  nofollow: boolean;
}

export interface ApiEnvelope<T> {
  data: T;
}

export interface ApiPaginatedEnvelope<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
  };
}

export interface Service {
  slug: string;
  name: string;
  tagline: string | null;
  summary: string | null;
  description: string | null;
  icon: string | null;
  cover_image: string | null;
  cover_image_alt: string | null;
  features: string[];
  tech_stack: string[];
  /** Present on the service detail endpoint when related work is loaded. */
  related_case_studies?: CaseStudySummary[];
  updated_at: string | null;
  seo: SeoMeta | null;
}

export type SystemType =
  | "saas_product"
  | "business_system"
  | "client_system"
  | "internal_product"
  | "platform"
  | "ai_system";

export interface System {
  slug: string;
  type: SystemType;
  category: string | null;
  name: string;
  tagline: string | null;
  short_description: string | null;
  full_description: string | null;
  problem: string | null;
  solution: string | null;
  features: string | null;
  business_outcomes: string | null;
  target_audience: string | null;
  tech_stack: string[];
  cover_image: string | null;
  cover_image_alt: string | null;
  gallery: string[];
  demo_url: string | null;
  live_url: string | null;
  is_featured: boolean;
  industries: Industry[];
  /** Lightweight refs -- see App\Http\Resources\V1\Public\CaseStudySummaryResource. */
  case_studies: CaseStudySummary[];
  updated_at: string | null;
  seo: SeoMeta | null;
}

/** See App\Http\Resources\V1\Public\SystemSummaryResource. */
export interface SystemSummary {
  slug: string;
  type: SystemType;
  name: string;
  tagline: string | null;
  cover_image: string | null;
  cover_image_alt: string | null;
}

/** See App\Http\Resources\V1\Public\CaseStudySummaryResource. */
export interface CaseStudySummary {
  slug: string;
  title: string;
  summary: string | null;
  client_name: string | null;
  cover_image: string | null;
  cover_image_alt: string | null;
  is_featured: boolean;
}

export interface Industry {
  slug: string;
  name: string;
  summary: string | null;
  description: string | null;
  icon: string | null;
  cover_image: string | null;
  cover_image_alt: string | null;
  updated_at: string | null;
  seo: SeoMeta | null;
}

export interface CaseStudy {
  slug: string;
  title: string;
  summary: string | null;
  context: string | null;
  problem: string | null;
  constraints: string | null;
  solution: string | null;
  architecture: string | null;
  outcomes: string | null;
  evidence: string | null;
  features: string | null;
  client_name: string | null;
  project_classification:
    | "custom_erp_crm"
    | "web_mobile_platform"
    | "ecommerce_business_website"
    | null;
  project_url: string | null;
  video_url: string | null;
  cover_image: string | null;
  cover_image_alt: string | null;
  gallery: string[];
  is_featured: boolean;
  service: Service | null;
  system: SystemSummary | null;
  industries: Industry[];
  updated_at: string | null;
  seo: SeoMeta | null;
}

export interface ArticleCategoryRef {
  slug: string;
  name: string;
}

export interface ArticleTagRef {
  slug: string;
  name: string;
}

export interface Article {
  slug: string;
  title: string;
  excerpt: string | null;
  body: string | null;
  cover_image: string | null;
  cover_image_alt: string | null;
  og_image: string | null;
  is_featured: boolean;
  reading_minutes: number;
  category: ArticleCategoryRef | null;
  tags: ArticleTagRef[];
  author: { name: string } | null;
  published_at: string | null;
  updated_content_at: string | null;
  seo: SeoMeta | null;
}

/** See App\Http\Controllers\Api\V1\Public\ArticleCategoryController. */
export interface ArticleCategory {
  slug: string;
  name: string;
  description: string | null;
  published_count: number;
}

/** See App\Http\Controllers\Api\V1\Public\SearchController. */
export interface SearchHit {
  slug: string;
  label: string;
  excerpt: string | null;
  path: string;
}

export interface SearchResults {
  query: string;
  results: Partial<
    Record<"services" | "systems" | "case_studies" | "industries" | "articles", SearchHit[]>
  >;
}

/** See App\Http\Controllers\Api\V1\Public\SettingsController. */
export interface CompanySettings {
  company_name: string | null;
  tagline: string | null;
  description: string | null;
  email: string | null;
  phone: string | null;
  whatsapp: string | null;
  address: string | null;
  social_links: Record<string, string>;
  booking_url: string | null;
  default_og_image: string | null;
  footer_note: string | null;
}

export interface PublicClaim {
  category: string;
  locale: string;
  claim_text: string;
}

export interface TeamMember {
  slug: string;
  full_name: string;
  first_name: string;
  last_name: string | null;
  position: string | null;
  bio: string | null;
  specialization: string | null;
  expertise: string[] | null;
  languages: string[] | null;
  location: string | null;
  photo: string | null;
  photo_alt: string | null;
  github_url: string | null;
  linkedin_url: string | null;
  is_founder: boolean;
  person_jsonld_eligible: boolean;
  claims: PublicClaim[];
}

export type TrustPageType =
  | "security"
  | "process"
  | "accessibility"
  | "technology"
  | "responsible_ai"
  | "engineering_standards"
  | "support"
  | "code_ip_ownership"
  | "data_privacy"
  | "company_delivery";

export interface TrustPageSection {
  heading: string;
  body: string;
}

export interface TrustPageFaq {
  question: string;
  answer: string;
}

export interface TrustPageCta {
  label: string;
  url: string;
}

export interface TrustPage {
  slug: string;
  page_type: TrustPageType;
  title: string;
  summary: string | null;
  sections: TrustPageSection[];
  faqs: TrustPageFaq[] | null;
  cta: TrustPageCta[] | null;
  show_in_nav: boolean;
  show_in_footer: boolean;
  noindex: boolean;
  reviewed_at: string | null;
  next_review_at: string | null;
  reviewer: string | null;
  updated_at: string | null;
  seo: SeoMeta | null;
  claims: PublicClaim[];
}

export interface Testimonial {
  author_name: string;
  author_title: string | null;
  company: string | null;
  company_logo: string | null;
  content: string;
  rating: number;
  given_at: string | null;
}

export interface Faq {
  question: string;
  answer: string;
  category: string | null;
}

export interface Redirect {
  from_path: string;
  to_path: string;
  status_code: number;
}

export interface HomePayload {
  services: Service[];
  featured_systems: System[];
  featured_case_studies: CaseStudy[];
  testimonials: Testimonial[];
  stats: {
    services: number;
    systems: number;
    case_studies: number;
    team: number;
  };
}

export type LeadIntent =
  | "start_project"
  | "request_quote"
  | "book_call"
  | "request_demo"
  | "general_contact"
  | "cost_estimate";

// --- Pricing & estimator ---

export type Currency = "USD" | "AED" | "SAR";

export interface EngagementModelPricing {
  currency: string;
  min_amount: number | null;
  max_amount: number | null;
  price_unit: string;
  billing_model: string;
  display_label: string | null;
  assumptions: string | null;
  exclusions: string | null;
  disclaimer: string | null;
}

export interface EngagementModel {
  slug: string;
  title: string;
  summary: string | null;
  buyer_fit: string | null;
  typical_scope: string | null;
  deliverables: string[];
  included_items: string[];
  excluded_items: string[];
  indicative_duration: string | null;
  cta_label: string | null;
  cta_intent: string;
  pricing_display_mode: string;
  billing_model: string;
  is_featured: boolean;
  /** null unless an approved price band exists for the requested currency. */
  pricing: EngagementModelPricing | null;
}

export interface PricingPayload {
  engagement_models: EngagementModel[];
  faqs: Faq[];
  estimator_available: boolean;
  currency: Currency;
  currencies: Currency[];
}

export interface EstimatorOption {
  key: string;
  label: string;
}

export interface EstimatorQuestion {
  key: string;
  step: number;
  type: "single_select" | "multi_select";
  prompt: string;
  help_text: string | null;
  is_required: boolean;
  show_if: { question: string; in: string[] } | null;
  options: EstimatorOption[];
}

export interface EstimatorConfig {
  available: boolean;
  version?: string;
  currencies?: Currency[];
  questions?: EstimatorQuestion[];
}

export interface EstimateCostDriver {
  key: string;
  label: string;
  weight: "low" | "medium" | "high";
}

export interface CostEstimateResult {
  public_uuid: string;
  currency: string;
  amount_min: number;
  amount_max: number;
  timeline_weeks_min: number;
  timeline_weeks_max: number;
  complexity: "standard" | "advanced" | "complex" | "enterprise";
  confidence: "low" | "medium" | "high";
  cost_drivers: EstimateCostDriver[];
  assumptions: string[];
  answers: Record<string, string | string[]>;
  recommended_engagement_model?: { slug: string; title: string } | null;
  status: string;
  has_lead: boolean;
  expires_at: string | null;
  created_at: string | null;
}

export interface EstimateCreatePayload {
  currency: Currency;
  locale: string;
  session_id?: string;
  answers: Record<string, string | string[]>;
}

export interface EstimateLeadPayload {
  name: string;
  email: string;
  phone?: string;
  company?: string;
  country?: string;
  summary?: string;
  requested_action?: "email_estimate" | "book_call" | "request_proposal" | "start_project" | "ask_question";
  source_page?: string;
  landing_page?: string;
  first_touch_at?: string;
  utm?: Record<string, string>;
  locale?: string;
  consent?: boolean;
  /** Honeypot -- must stay empty. */
  website?: string;
}

export interface LeadPayload {
  intent?: LeadIntent;
  name: string;
  email: string;
  phone?: string;
  whatsapp?: string;
  company?: string;
  company_size?: string;
  role_title?: string;
  country?: string;
  project_type?: string;
  industry?: string;
  system_type?: string;
  budget_range?: string;
  timeline?: string;
  summary?: string;
  pain_points?: string;
  preferred_contact_method?: "email" | "phone" | "whatsapp";
  consent?: boolean;
  requested_service_slug?: string;
  requested_system_slug?: string;
  source_page?: string;
  landing_page?: string;
  first_touch_at?: string;
  utm?: Record<string, string>;
  locale?: string;
  /** Honeypot -- must stay empty. A hidden field in the real form. */
  website?: string;
}
