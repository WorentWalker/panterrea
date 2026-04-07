const TABS_SELECTOR = '.js-tabs';
const TAB_SELECTOR = '[role=tab]';
const TABPANEL_SELECTOR = '[role=tabpanel]';
const DATA_TABPANEL_ID = 'data-tabpanel-id';
const ACTIVE_EL_CLASS = 'active';

class Tabs {
    /**
     * @param {HTMLElement} container - Tabs container
     */
    constructor(container) {
        this.container = container;
        this.tablist = this.container.querySelector('[role="tablist"]');
        this.tabs = this.container.querySelectorAll(TAB_SELECTOR);
        this.tabpanels = this.container.querySelectorAll(TABPANEL_SELECTOR);

        if (!this.tablist || this.tabs.length === 0 || this.tabpanels.length === 0) {
            console.error('Tabs: Missing required elements (tablist, tabs, or tabpanels).');
            return;
        }

        this.init();
    }

    init() {
        this.tabs.forEach((tab) => {
            tab.addEventListener('click', () => this.onTabClick(tab));
        });
    }

    onTabClick(tab) {
        this.tabs.forEach((t) => t.classList.remove(ACTIVE_EL_CLASS));
        this.tabpanels.forEach((panel) => panel.classList.remove(ACTIVE_EL_CLASS));

        tab.classList.add(ACTIVE_EL_CLASS);

        const tabpanelId = tab.getAttribute(DATA_TABPANEL_ID);
        const tabpanel = this.container.querySelector(`#${tabpanelId}`);

        if (!tabpanel) {
            console.error(`Tabs: Tabpanel with id "${tabpanelId}" not found.`);
            return;
        }

        tabpanel.classList.add(ACTIVE_EL_CLASS);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const tabsContainers = document.querySelectorAll(TABS_SELECTOR);
    tabsContainers.forEach((container) => new Tabs(container));
});