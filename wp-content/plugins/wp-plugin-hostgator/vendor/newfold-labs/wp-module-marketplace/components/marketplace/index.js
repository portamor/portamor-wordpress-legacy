import { default as MarketplaceList } from '../marketplaceList/';
import { default as MarketplaceIsLoading } from '../marketplaceIsLoading/';

/**
 * Marketplace Module
 * For use in brand app to display marketplace
 * 
 * @param {*} props 
 * @returns 
 */
 const Marketplace = ({methods, constants, Components, ...props}) => {
	const [ isLoading, setIsLoading ] = methods.useState( true );
	const [ isError, setIsError ] = methods.useState( false );
	const [ marketplaceCategories, setMarketplaceCategories ] = methods.useState( [] );
	const [ marketplaceItems, setMarketplaceItems ] = methods.useState( [] );
	const [ initialTab, setInitialTab ] = methods.useState();
	const navigate = methods.useNavigate();
	const location = methods.useLocation();

	/**
	 * Update url when navigating between tabs
	 * @param string tab name 
	 */
	const onTabNavigate = ( tabName ) => {
		navigate( '/marketplace/' + tabName, { replace: true } );
	};

	/**
	 * on mount load all marketplace data from module api
	 */
	methods.useEffect(() => {
		methods.apiFetch( {
			url: `${constants.resturl}/newfold-marketplace/v1/marketplace`
		}).then( ( response ) => {
			// check response for data
			if ( ! response.hasOwnProperty('categories') || ! response.hasOwnProperty('products') ) {
				setIsError( true );
			} else {
				setMarketplaceItems( response.products.data );
				setMarketplaceCategories( validateCategories(response.categories.data) );
			}
		});
	}, [] );

	/**
	 * When marketplaceItems changes
	 * verify that there are products
	 */
	 methods.useEffect(() => {
		// only after a response
		if ( !isLoading ) {
			// if no marketplace items, display error
			if ( marketplaceItems.length < 1 ) {
				setIsError( true );
			} else {
				setIsError( false );
			}
		}
	}, [ marketplaceItems ] );

	/**
	 * When marketplaceCategories changes
	 * verify that the tab is a category
	 */
	 methods.useEffect(() => {
		// only before rendered, but after categories are populated
		if ( isLoading && marketplaceCategories.length > 1 ) {
			// read initial tab from path
			if ( location.pathname.includes( 'marketplace/' ) ) {
				const urlpath = location.pathname.substring( 
					location.pathname.lastIndexOf( '/' ) + 1
				);
				// make sure a category exists for that path
				if ( urlpath && marketplaceCategories.filter(cat => cat.name === urlpath ).length == 0 ) {
					// if not found, set to featured category
					setInitialTab( 0 );
				} else {
					// if found, set that to the initial tab
					setInitialTab( urlpath );
				}
			}
			setIsLoading( false );
			applyStyles();
		}
	}, [ marketplaceCategories ] );

	/**
	 * Validate provided category data
	 * @param Array categories 
	 * @returns 
	 */
	const validateCategories = ( categories ) => {
		
		if ( ! categories.length ) {
			return [];
		}
		
		let thecategories = [];
		categories.forEach((cat)=>{
			cat.currentCount = constants.perPage;
			cat.className = 'newfold-marketplace-tab-'+cat.name;

			if ( cat.products_count > 0 ) {
				thecategories.push(cat);
			}
		});
		
		return thecategories;
	};

	/**
	 * Save a potential updated display counts per category
	 * @param string categoryName 
	 * @param Number newCount 
	 */
	const saveCategoryDisplayCount = (categoryName, newCount) => {
		let updatedMarketplaceCategories = [...marketplaceCategories];
		// find matching cat, and update perPage amount
		updatedMarketplaceCategories.forEach( (cat) => {
			if (cat.name === categoryName ) {
				cat.currentCount = newCount;
			}
		});
		setMarketplaceCategories( updatedMarketplaceCategories );
	};

	/**
	 * Apply styles if they exist
	 */
	 const applyStyles = () => {
		if ( marketplaceCategories ) {
			marketplaceCategories.forEach( (category) => {
				if( 
					category.styles && // category has styles
					!document.querySelector('[data-styleid="' + category.className + '"]') // not already added
				) {
					const style = document.createElement("style")
					style.textContent = category.styles;
					style.dataset.styleid = category.className;
					document.head.appendChild(style);
				}
			});
		}
	};

	/**
	 * render marketplace preloader
	 * 
	 * @returns React Component
	 */
	 const renderSkeleton = () => {
		// render default skeleton
		return <MarketplaceIsLoading />;
	}


	return (
		<div className={methods.classnames('newfold-marketplace-wrapper')}>
			{ isLoading && 
				renderSkeleton()
			}
			{ isError && 
				<h3>Oops, there was an error loading the marketplace, please try again later.</h3>
			}
			{ !isLoading && !isError &&
				<Components.TabPanel
					className="newfold-marketplace-tabs"
					activeClass="current-tab"
					orientation="horizontal"
					initialTabName={ initialTab }
					onSelect={ onTabNavigate }
					tabs={ marketplaceCategories }
				>
					{ ( tab ) => <MarketplaceList
						marketplaceItems={marketplaceItems}
						category={tab}
						Components={Components}
						methods={methods}
						constants={constants}
						currentCount={tab.currentCount}
						saveCategoryDisplayCount={saveCategoryDisplayCount}
					/> }
				</Components.TabPanel>
			}
		</div>
	)

};

export default Marketplace;