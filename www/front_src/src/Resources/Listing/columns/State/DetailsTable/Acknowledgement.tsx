/* eslint-disable react/prop-types */
/* eslint-disable react/no-unused-prop-types */
import * as React from 'react';

import parse from 'html-react-parser';
import DOMPurify from 'dompurify';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@material-ui/core';

import { ColumnType } from '@centreon/ui';

import {
  labelAuthor,
  labelComment,
  labelEntryTime,
  labelPersistent,
  labelSticky,
} from '../../../../translatedLabels';
import { getFormattedDateTime } from '../../../../dateTime';

import DetailsTable, { DetailsTableProps, getYesNoLabel } from '.';

const useStyles = makeStyles({
  comment: {
    display: 'block',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
});

interface AcknowledgementDetails {
  author_name: string;
  comment: string;
  entry_time: string;
  id: number;
  is_persistent_comment: string;
  is_sticky: string;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const AcknowledgementDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const columns = [
    {
      getContent: ({ author_name }): string => author_name,
      id: 'author',
      label: t(labelAuthor),
      type: ColumnType.string,
      width: 100,
    },
    {
      getContent: ({ entry_time }): string => getFormattedDateTime(entry_time),
      id: 'entry_time',
      label: t(labelEntryTime),
      type: ColumnType.string,
      width: 150,
    },
    {
      getContent: ({ is_persistent_comment }): string =>
        t(getYesNoLabel(is_persistent_comment)),
      id: 'is_persistent',
      label: t(labelPersistent),
      type: ColumnType.string,
      width: 100,
    },
    {
      getContent: ({ is_sticky }): string => t(getYesNoLabel(is_sticky)),
      id: 'is_sticky',
      label: t(labelSticky),
      type: ColumnType.string,
      width: 100,
    },

    {
      getContent: ({ comment }: AcknowledgementDetails): JSX.Element => {
        return (
          <span className={classes.comment}>
            {parse(DOMPurify.sanitize(comment))}
          </span>
        );
      },
      id: 'comment',
      label: t(labelComment),
      type: ColumnType.string,
      width: 250,
    },
  ];

  return (
    <DetailsTable<AcknowledgementDetails>
      columns={columns}
      endpoint={endpoint}
    />
  );
};

export default AcknowledgementDetailsTable;
