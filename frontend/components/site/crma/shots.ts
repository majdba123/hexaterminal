/**
 * CRMA screenshot registry (public/work/crma).
 *
 * Every file is an unmodified capture of the running CRMA application on
 * seeded, fictionalised demo data, re-encoded to WebP (2400px desktop masters
 * from 3200x2000 captures, 780px mobile masters from 1170x2532). The only
 * composed asset is `teaser-overview`, the HexaTerminal-branded card used as
 * the home and index teaser. Intrinsic sizes are declared here so next/image
 * reserves the box and nothing shifts while a screenshot decodes.
 *
 * Deliberately NOT shipped from the source folder: the sessions screen (it
 * shows a demo-environment driver warning), the customer-360 screen (seeded
 * lorem ipsum), every personal-portfolio variant, and the composed slides
 * whose copy has not been re-verified against the repository.
 */
const DESKTOP = { width: 2400, height: 1500 } as const;
const MOBILE = { width: 780, height: 1688 } as const;

export const CRMA_SHOTS = {
  dashboard: { file: "01-dashboard", ...DESKTOP },
  pipeline: { file: "02-deal-pipeline", ...DESKTOP },
  dealWorkspace: { file: "03-deal-workspace", ...DESKTOP },
  contacts: { file: "04-contacts", ...DESKTOP },
  leads: { file: "05-leads", ...DESKTOP },
  tasks: { file: "06-tasks", ...DESKTOP },
  analytics: { file: "07-analytics", ...DESKTOP },
  reports: { file: "08-reports", ...DESKTOP },
  roles: { file: "09-roles-permissions", ...DESKTOP },
  aiSettings: { file: "10-ai-settings", ...DESKTOP },
  mcpConnectors: { file: "11-mcp-connectors", ...DESKTOP },
  activity: { file: "12-activity-audit", ...DESKTOP },
  workflows: { file: "13-workflows", ...DESKTOP },
  workflowBuilder: { file: "14-workflow-builder", ...DESKTOP },
  aiInbox: { file: "15-ai-inbox", ...DESKTOP },
  teams: { file: "17-teams", ...DESKTOP },
  knowledgeBase: { file: "18-knowledge-base", ...DESKTOP },
  copilotApproval: { file: "19-copilot-approval", ...DESKTOP },
  copilotTask: { file: "20-copilot-task-proposal", ...DESKTOP },
  actionCenter: { file: "21-ai-action-center", ...DESKTOP },
  rtlDashboard: { file: "23-arabic-rtl-dashboard", ...DESKTOP },
  rtlPipeline: { file: "24-arabic-rtl-pipeline", ...DESKTOP },
  rtlActionCenter: { file: "25-arabic-rtl-action-center", ...DESKTOP },
  mDashboard: { file: "m01-dashboard", ...MOBILE },
  mPipeline: { file: "m02-pipeline", ...MOBILE },
  mActions: { file: "m04-ai-actions", ...MOBILE },
  mDeal: { file: "m06-deal-workspace", ...MOBILE },
  teaser: { file: "teaser-overview", width: 1200, height: 900 },
} as const;

export type CrmaShotKey = keyof typeof CRMA_SHOTS;

export function crmaSrc(key: CrmaShotKey): string {
  return `/work/crma/${CRMA_SHOTS[key].file}.webp`;
}

/** Social card (1200x630), HexaTerminal-branded. */
export const CRMA_OG_IMAGE = "/work/crma/og.png";

export const CRMA_PATH = "/case-studies/crma";
