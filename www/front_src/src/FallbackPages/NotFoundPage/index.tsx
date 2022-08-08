import CommonFallback from '../CommonFallback';

import { labelThisPageCouldNotBeFound } from './translatedLabels';

const NotFoundPage = (): JSX.Element => (
  <CommonFallback message={labelThisPageCouldNotBeFound} statusCode={404} />
);

export default NotFoundPage;
