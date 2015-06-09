# Personalization

Personalization splits into several subsystems:

- Content personalization
- Products recommendation(cross-sell, upselling, reranking, etc.)
- Personalized and non-personalized emails by triggers
- In-site recommendation by triggers
- Contextual navigation reranking

## Core principles

All recommendation and personalization systems need to know some data about user, his visit-session context and history.

Therefore personalization core consists of:

- Storage database:
	+ Fast database(in-memory preferred) for sessions
	+ Big-data database for normalized history and events data
- Configurable events subsystem:
	+ Event can be triggered either by application or by JavaScript(for example assume page is viewed if it's scrolled and view duration was > 2 seconds)
	+ Events can be configured in site's backend
	+ Personalization backend can turn on only needed events, but storing of needed events can be forced in backend configuration
- Personalization backend for each of subsystems:
	+ Accepts and processes data
	+ Can inject code and/or javascript into the specified view on event or act as decorator(**Warning! Widgets decorators needed here**)
	+ Can manipulate core queries through events(for example reranking products on shop/product/list)

## Recommendation framework

Recommender engine injects into existing base widget, views and controllers through Events.