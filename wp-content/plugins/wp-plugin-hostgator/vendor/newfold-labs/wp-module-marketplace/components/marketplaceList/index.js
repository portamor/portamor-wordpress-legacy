
import { default as MarketplaceItem } from '../marketplaceItem/';

/**
 * MarketplaceList Component
 * For use in Marketplace to display a list of marketplace items
 * 
 * @param {*} props 
 * @returns 
 */
const MarketplaceList = ({ marketplaceItems, currentCount, category, Components, methods, constants, saveCategoryDisplayCount }) => {
	const [ itemsCount, setItemsCount ] = methods.useState( currentCount );
	const [ currentItems, setCurrentItems ] = methods.useState( [] );
	const [ activeItems, setActiveItems ] = methods.useState( [] )

	/**
	 * Filter Products By Category - this ensures only this category products is listed here, it gets us current items
	 * @param Array items - the products
	 * @param string category - the category to filter by 
	 * @returns 
	 */
	const filterProductsByCategory = (items, category) => {
		return items.filter((item) => {
			return item.categories.includes( category.title );
		});
	};

	/**
	 * Set Product List Length - this controls how many products are displayed in the list, it gets us active current items
	 * @param Array items 
	 * @param Number itemsCount 
	 * @returns 
	 */
	const setProductListCount = (items, itemsCount) => {
		let count = 0;
		return items.filter((item) => {
			count++;
			return count <= itemsCount;
		});
	};

	/**
	 * increment itemCount by perPage amount
	 */
	const loadMoreClick = () => {
		setItemsCount( itemsCount + constants.perPage );
	};

	/**
	 * init method - filter products
	 */
	methods.useEffect(() => {
		setCurrentItems( filterProductsByCategory(marketplaceItems, category) );
	}, []);

	/**
	 * recalculate activeItems if currentItems or itemsCount changes
	 */
	methods.useEffect(() => {
		setActiveItems( setProductListCount(currentItems, itemsCount) );
	}, [ currentItems, itemsCount ] );

	/**
	 * pass up itemsCount for this list when it changes
	 * this is so users don't need to load more every time they click back into a category
	 */
	methods.useEffect(() => {
		saveCategoryDisplayCount( category.name, itemsCount );
	}, [ itemsCount ] );

	return (
		<div className={ `marketplace-list marketplace-list-${ category.name }` }>
			<div className="grid col2">
				{ activeItems.length > 0 && activeItems.map((item) => (
						<MarketplaceItem
							key={item.hash} 
							item={item}
							Components={Components}
							methods={methods}
							constants={constants}
						/>
					))
				}
				{ !activeItems.length &&
					<p>Sorry, no marketplace items. Please, try again later.</p>
				}
			</div>
			{ currentItems && currentItems.length > itemsCount &&
				<div style={{ display: 'flex', margin: '1rem 0'}}>
					<Components.Button
						onClick={loadMoreClick}
						variant="primary" 
						className="align-center"
						style={{margin: 'auto'}}
					>
						Load More
					</Components.Button>
				</div>
			}
		</div>
	)
};

export default MarketplaceList;