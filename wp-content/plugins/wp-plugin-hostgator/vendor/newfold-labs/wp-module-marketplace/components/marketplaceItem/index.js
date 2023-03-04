/**
 * MarketplaceItem Component
 * For use in Marketplace to display marketplace items
 * 
 * @param {*} props 
 * @returns 
 */
 const MarketplaceItem = ({ item, Components, methods, constants }) => {

	/**
	 * Send events to the WP REST API
	 *
	 * @param {Object} event The event data to be tracked.
	 */
	 const sendEvent = (event)  => {
		event.data = event.data || {};
		event.data.page = window.location.href;
		methods.apiFetch({
			url: `${constants.resturl}${constants.eventendpoint}`,
			method: 'POST', 
			data: event
		});
	}

	/**
	 * Handle button clicks
	 * @param Event event 
	 * @returns 
	 */
	const onButtonNavigate = ( event ) => {
		if ( event.keycode && ENTER !== event.keycode ) {
			return;
		}
		sendEvent({
			action: 'newfold-marketplaceitem-click',
			data: {
				element: 'button',
				label: event.target.innerText,
				productId: item.id,
			}
		})
	}

	/**
	 * Handle link clicks
	 * @param Event event 
	 * @returns 
	 */
	const onAnchorNavigate = ( event ) => {
		if ( event.keycode && ENTER !== event.keycode ) {
			return;
		}
		sendEvent({
			action: 'newfold-marketplaceitem-click',
			data: {
				element: 'a',
				href: event.target.getAttribute('href'),
				label: event.target.innerText,
				productId: item.id,
			}
		})
	}
	
	/**
	 * initial set up - adding event listeners
	 */
	methods.useEffect(() => {
		const itemContainer   = document.getElementById(`marketplace-item-${ item.id }`);
		const itemButtons     = Array.from(itemContainer.querySelectorAll('button'));
		const itemAnchors     = Array.from(itemContainer.querySelectorAll('a'));

		if (itemButtons.length) {
			itemButtons.forEach(
				button => {
					if (button.getAttribute('data-action') !== 'close') {
						button.addEventListener('click', onButtonNavigate);
						button.addEventListener('onkeydown', onButtonNavigate);
					}
				}
			)
		}

		if (itemAnchors.length) {
			itemAnchors.forEach(
				link => {
					if (link.getAttribute('data-action') !== 'close') {
						link.addEventListener('click', onAnchorNavigate);
						link.addEventListener('onkeydown', onAnchorNavigate);
					}
				}
			)
		}

		// unmount remove event listeners
		return () => {
			if (itemButtons.length) {
				itemButtons.forEach(
					button => {
						if (button.getAttribute('data-action') !== 'close') {
							button.removeEventListener('click', onButtonNavigate);
							button.removeEventListener('onkeydown', onButtonNavigate);
						}
					}
				)
			}
			if (itemAnchors.length) {
				itemAnchors.forEach(
					link => {
						if (link.getAttribute('data-action') !== 'close') {
							link.removeEventListener('click', onAnchorNavigate);
							link.removeEventListener('onkeydown', onAnchorNavigate);
						}
					}
				)
			}
		}
	}, [] );

	const renderCTAs = (item) => {
		let primaryCTA, secondaryCTA;
		if ( item.primaryUrl && item.primaryCallToAction ) {
			primaryCTA = (
				<Components.Button
					variant="primary"
					className="primary-cta"
					target="_blank"
					href={ item.primaryUrl }
					// create CTB button attributes
					{... constants.supportsCTB && item.clickToBuyId ? { 
							'data-action': "load-nfd-ctb",
							'data-ctb-id': item.clickToBuyId
						} : {}
					}
				>
					{ item.primaryCallToAction }
				</Components.Button>
			);
		}

		if ( item.secondaryCallToAction && item.secondaryUrl ) {
			secondaryCTA = (
				<Components.Button 
					variant="secondary"
					className="secondary-cta"
					target="_blank"
					href={ item.secondaryUrl }
				>
					{ item.secondaryCallToAction }
				</Components.Button>
			);
		}
		return( 
			<>
				{ primaryCTA }
				{ secondaryCTA }
			</>
		);
	};

	const renderPrice = (item) => {
		let pricewrap, price, fullprice;
		if ( item.price > 0 && item.price_formatted ) {
			price =  (
				<div className="price">{ item.price_formatted }</div>
			);
			if ( item.full_price > 0 && item.full_price_formatted ) {
				fullprice =  (
					<s className="price full-price">{ item.full_price_formatted }</s>
				);	
				pricewrap = (
					<div className="price-wrap has-full-price">
						{ fullprice }
						{ price }
					</div>
				);
			} else {
				pricewrap = (
					<div className="price-wrap">
						{ price }
					</div>
				);
			}
		}
		return pricewrap;
	};

	return (
		<Components.Card className={ `marketplace-item marketplace-item-${ item.id } ${ item.full_price ? "product-has-full-price" : ""}` } id={`marketplace-item-${ item.id }`}>
			{ item.productThumbnailUrl && (
				<Components.CardMedia>
					<img src={ item.productThumbnailUrl } alt={ item.name + ' thumbnail' } />
				</Components.CardMedia>
			) }
			<Components.CardHeader>
				<h2>{ item.name }</h2>
				{ renderPrice(item) }
			</Components.CardHeader>
			{ item.description && 
				<Components.CardBody 
					// Comes from internal source - let's trust ourselves (for now)
					dangerouslySetInnerHTML={{ __html: item.description }} 
				/>
			}
			<Components.CardFooter>
				{ renderCTAs(item) }
			</Components.CardFooter>
		</Components.Card>
	);
};

export default MarketplaceItem;
