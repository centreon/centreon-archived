import CommonFallback from '../CommonFallback';

import { labelYouAreNotAllowedToSeeThisPage } from './translatedLabels';

const NotAllowedPage = (): JSX.Element => (
  <CommonFallback message={labelYouAreNotAllowedToSeeThisPage} />
);

export default NotAllowedPage;
