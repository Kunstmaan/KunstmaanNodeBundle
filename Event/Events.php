<?php

namespace Kunstmaan\NodeBundle\Event;

/**
 * Events
 */
class Events
{

    /**
     * The addNode event occurs for a given node, after it's being created.
     *
     * @var string
     */
    const ADD_NODE = 'kunstmaan_node.addNode';

    /**
     * The preUnPublish event occurs for a given node, before it's unpublished.
     *
     * @var string
     */
    const PRE_UNPUBLISH = 'kunstmaan_node.preUnPublish';

    /**
     * The postUnPublish event occurs for a given node, after it's unpublished.
     *
     * @var string
     */
    const POST_UNPUBLISH = 'kunstmaan_node.postUnPublish';

    /**
     * The prePublish event occurs for a given node, before it's published.
     *
     * @var string
     */
    const PRE_PUBLISH = 'kunstmaan_node.prePublish';

    /**
     * The postPublish event occurs for a given node, after it's published.
     *
     * @var string
     */
    const POST_PUBLISH = 'kunstmaan_node.postPublish';

    /**
     * The preDelete event occurs for a given node, before it's deleted.
     *
     * @var string
     */
    const PRE_DELETE = 'kunstmaan_node.preDelete';

    /**
     * The postDelete event occurs for a given node, after it's deleted.
     *
     * @var string
     */
    const POST_DELETE = 'kunstmaan_node.postDelete';

    /**
     * The adaptForm event occurs when building the form for the node.
     *
     * @var string
     */
    const ADAPT_FORM = 'kunstmaan_node.adaptForm';

    /**
     * The postPersist event occurs for a given node, before the node is persisted.
     *
     * @var string
     */
    const PRE_PERSIST = 'kunstmaan_node.prePersist';

    /**
     * The postPersist event occurs for a given node, after the node is persisted.
     *
     * @var string
     */
    const POST_PERSIST = 'kunstmaan_node.postPersist';

    /**
     * The createPublicVersion event occurs for a given node, when a public version is created.
     *
     * @var string
     */
    const CREATE_PUBLIC_VERSION = 'kunstmaan_node.createPublicVersion';

    /**
     * The createDraftVersion event occurs for a given node, when a draft version is created.
     *
     * @var string
     */
    const CREATE_DRAFT_VERSION = 'kunstmaan_node.createDraftVersion';

    /**
     * The copyPageTranslation event occurs for a given node, after a page translation has been copied.
     *
     * @var string
     */
    const COPY_PAGE_TRANSLATION = 'kunstmaan_node.copyPageTranslation';

    /**
     * The emptyPageTranslation event occurs for a given node, after a new page translation is created.
     *
     * @var string
     */
    const ADD_EMPTY_PAGE_TRANSLATION = 'kunstmaan_node.emptyPageTranslation';

    /**
     * This event will be triggered when creating the menu for the page sub actions.
     * It is possible to change this menu using this event.
     *
     * @var string
     */
    const CONFIGURE_SUB_ACTION_MENU = 'kunstmaan_node.configureSubActionMenu';

    /**
     * This event will be triggered when creating the menu for the page actions.
     * It is possible to change this menu using this event.
     *
     * @var string
     */
    const CONFIGURE_ACTION_MENU = 'kunstmaan_node.configureActionMenu';

}
