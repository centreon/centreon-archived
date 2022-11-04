import { equals, prop, propEq, reject, sortBy, lte, length } from 'ramda';

import { ResourceDetails } from '../../../../../Details/models';
import { Resource } from '../../../../../models';
import { Line } from '../../../models';

import { getDisplayAdditionalLinesCondition } from './AnomalyDetectionAdditionalLines';

interface NewLines {
  lines: Array<Line>;
  resource: ResourceDetails | Resource;
}

interface NewLinesAnomalyDetection {
  newLines: Array<Line>;
  newSortedLines: Array<Line>;
}

export const getNewLinesAnomalyDetection = ({
  lines,
  resource,
}: NewLines): NewLinesAnomalyDetection => {
  const sortedLines = sortBy(prop('name'), lines);

  const originMetric = sortedLines.map(({ metric }) =>
    metric.includes('_upper_thresholds')
      ? metric.replace('_upper_thresholds', '')
      : null,
  );
  const lineOriginMetric = sortedLines.filter((item) => {
    const name = originMetric.filter((element) => element);

    return equals(item.metric, name[0]);
  });
  const linesThreshold = sortedLines.filter(({ metric }) =>
    metric.includes('thresholds'),
  );
  const newSortedLines = getDisplayAdditionalLinesCondition?.condition(resource)
    ? [...linesThreshold, ...lineOriginMetric]
    : sortedLines;

  const displayedLines = reject(propEq('display', false), newSortedLines);

  return { newLines: displayedLines, newSortedLines };
};

export const displayAdditionalLines = ({
  lines,
  resource,
}: {
  lines: Array<Line>;
  resource: Resource | ResourceDetails;
}): boolean => {
  const isLegendClicked = lte(length(lines), 1);

  const displayAdditionalLinesCondition =
    getDisplayAdditionalLinesCondition?.condition(resource) || false;

  return displayAdditionalLinesCondition && !isLegendClicked;
};
